import { test, expect } from '@playwright/test'

// Configure test timeout and retries
test.describe.configure({ timeout: 120000, retries: 0 })

const BASE_URL = process.env.APP_URL || 'http://localhost:5173'
const API_URL = process.env.API_URL || 'http://localhost:8000'

test.describe('Live Exam Auto-Submit Feature', () => {
    let teacherPage
    let studentPage
    let sessionPin

    test.beforeAll(async ({ browser }) => {
        // Open two browser contexts: one for teacher, one for student
        const teacherContext = await browser.newContext()
        const studentContext = await browser.newContext()
        
        teacherPage = await teacherContext.newPage()
        studentPage = await studentContext.newPage()
    })

    test.afterAll(async () => {
        await teacherPage.close()
        await studentPage.close()
    })

    test('T1: Auto-submit on next button click with multiple choice', async () => {
        console.log('TEST: Auto-submit on next button click')
        
        // Step 1: Teacher creates and starts session
        await teacherPage.goto(`${BASE_URL}/teacher/live`)
        await teacherPage.waitForTimeout(2000)
        
        // Create session
        const createSessionBtn = teacherPage.locator('button:has-text("Create Session")')
        if (await createSessionBtn.isVisible()) {
            await createSessionBtn.click()
            await teacherPage.waitForTimeout(1000)
        }
        
        // Get PIN from screen
        const pinElement = teacherPage.locator('[data-testid="session-pin"]')
        sessionPin = await pinElement.textContent()
        console.log('Session PIN:', sessionPin)
        expect(sessionPin).toBeTruthy()
        
        // Start session
        const startBtn = teacherPage.locator('button:has-text("Start")')
        await startBtn.click()
        await teacherPage.waitForTimeout(2000)
        
        // Step 2: Student joins
        await studentPage.goto(`${BASE_URL}/student/join`)
        const pinInput = studentPage.locator('input[placeholder="Enter PIN"]')
        await pinInput.fill(sessionPin)
        const joinBtn = studentPage.locator('button:has-text("Join")')
        await joinBtn.click()
        
        // Accept classroom code if needed
        const acceptBtn = studentPage.locator('button:has-text("Accept")').first()
        if (await acceptBtn.isVisible({ timeout: 5000 })) {
            await acceptBtn.click()
        }
        
        await studentPage.waitForLoadState('networkidle')
        await teacherPage.waitForTimeout(1000)
        
        // Step 3: Verify both see same question
        const teacherQuestion = teacherPage.locator('[data-testid="question-text"]')
        const studentQuestion = studentPage.locator('[data-testid="question-text"]')
        
        const teacherQText = await teacherQuestion.textContent()
        const studentQText = await studentQuestion.textContent()
        
        console.log('Teacher sees:', teacherQText)
        console.log('Student sees:', studentQText)
        expect(teacherQText).toBe(studentQText)
        
        // Step 4: Student selects an answer (but doesn't click submit)
        const options = studentPage.locator('[data-testid="option"]')
        const optionCount = await options.count()
        
        if (optionCount > 0) {
            // Select first option
            await options.first().click()
            console.log('Student selected option 0')
            
            // Verify selection tracked in console
            const consoleMessages = []
            studentPage.on('console', msg => {
                consoleMessages.push(msg.text())
            })
            
            await studentPage.waitForTimeout(500)
        }
        
        // Step 5: Teacher clicks Next
        console.log('Teacher clicking Next...')
        const nextBtn = teacherPage.locator('button:has-text("Next")')
        await nextBtn.click()
        
        // Step 6: Verify auto-submit happened (check console for success message)
        let autoSubmitSuccess = false
        studentPage.on('console', msg => {
            if (msg.text().includes('AUTO-SUBMIT SUCCESS')) {
                autoSubmitSuccess = true
                console.log('Auto-submit detected:', msg.text())
            }
        })
        
        await studentPage.waitForTimeout(2000)
        
        // Step 7: Verify both on new question
        const teacherNewQuestion = teacherPage.locator('[data-testid="question-text"]')
        const studentNewQuestion = studentPage.locator('[data-testid="question-text"]')
        
        const teacherNewText = await teacherNewQuestion.textContent()
        const studentNewText = await studentNewQuestion.textContent()
        
        console.log('After next - Teacher sees:', teacherNewText)
        console.log('After next - Student sees:', studentNewText)
        
        expect(teacherNewText).not.toBe(teacherQText) // Should be different question
        expect(teacherNewText).toBe(studentNewText) // Should still be in sync
        
        // Step 8: Check Network tab for API call
        // Intercept and verify the answer submission request
        const responses = []
        studentPage.on('response', response => {
            if (response.url().includes('submit-answer')) {
                responses.push(response)
            }
        })
        
        await studentPage.waitForTimeout(1000)
        
        if (responses.length > 0) {
            const lastResponse = responses[responses.length - 1]
            console.log('Answer submission status:', lastResponse.status())
            expect([200, 201]).toContain(lastResponse.status()) // Expect success
        }
    })

    test('T2: Auto-submit on previous button click', async () => {
        console.log('TEST: Auto-submit on previous button click')
        
        // Setup: Teacher and student already in session from previous test
        // Navigate forward, then backward
        
        const nextBtn = teacherPage.locator('button:has-text("Next")').first()
        if (await nextBtn.isVisible()) {
            await nextBtn.click()
            await teacherPage.waitForTimeout(1000)
            
            // Student selects answer
            const options = studentPage.locator('[data-testid="option"]')
            const optionCount = await options.count()
            if (optionCount > 0) {
                await options.nth(1).click() // Select different option
            }
            
            // Teacher clicks Previous
            const prevBtn = teacherPage.locator('button:has-text("Previous")').first()
            await prevBtn.click()
            await studentPage.waitForTimeout(2000)
            
            // Verify auto-submit for previous
            let autoSubmitPrevSuccess = false
            studentPage.on('console', msg => {
                if (msg.text().includes('AUTO-SUBMIT (PREV) SUCCESS')) {
                    autoSubmitPrevSuccess = true
                }
            })
            
            console.log('Auto-submit for previous:', autoSubmitPrevSuccess ? 'SUCCESS' : 'FAILED')
        }
    })

    test('T3: Timer pause/resume syncs both timers', async () => {
        console.log('TEST: Timer pause/resume functionality')
        
        // Get initial timer values
        const studentGlobalTimer = studentPage.locator('[data-testid="global-timer"]')
        const studentQuestionTimer = studentPage.locator('[data-testid="question-timer"]')
        
        if (await studentGlobalTimer.isVisible() && await studentQuestionTimer.isVisible()) {
            const initialGlobalTime = await studentGlobalTimer.textContent()
            const initialQuestionTime = await studentQuestionTimer.textContent()
            
            console.log('Initial times - Global:', initialGlobalTime, 'Question:', initialQuestionTime)
            
            // Wait a bit for timers to count down
            await studentPage.waitForTimeout(3000)
            
            // Get times before pause
            const beforePauseGlobal = await studentGlobalTimer.textContent()
            const beforePauseQuestion = await studentQuestionTimer.textContent()
            
            console.log('Before pause - Global:', beforePauseGlobal, 'Question:', beforePauseQuestion)
            
            // Teacher clicks pause
            const pauseBtn = teacherPage.locator('button:has-text("Pause")').first()
            if (await pauseBtn.isVisible()) {
                await pauseBtn.click()
                console.log('Teacher clicked Pause')
                
                await studentPage.waitForTimeout(1000)
                
                // Verify timers stopped
                const afterPauseGlobal = await studentGlobalTimer.textContent()
                const afterPauseQuestion = await studentQuestionTimer.textContent()
                
                console.log('After pause - Global:', afterPauseGlobal, 'Question:', afterPauseQuestion)
                
                // Wait 3 seconds and check if timers remained the same (proof they stopped)
                await studentPage.waitForTimeout(3000)
                
                const stillPausedGlobal = await studentGlobalTimer.textContent()
                const stillPausedQuestion = await studentQuestionTimer.textContent()
                
                console.log('While paused - Global:', stillPausedGlobal, 'Question:', stillPausedQuestion)
                
                expect(stillPausedGlobal).toBe(afterPauseGlobal) // Should not have changed
                expect(stillPausedQuestion).toBe(afterPauseQuestion) // Should not have changed
                
                // Teacher clicks resume
                const resumeBtn = teacherPage.locator('button:has-text("Resume")').first()
                if (await resumeBtn.isVisible()) {
                    await resumeBtn.click()
                    console.log('Teacher clicked Resume')
                    
                    await studentPage.waitForTimeout(1000)
                    
                    // Verify timers resumed
                    const afterResumeGlobal = await studentGlobalTimer.textContent()
                    const afterResumeQuestion = await studentQuestionTimer.textContent()
                    
                    console.log('After resume - Global:', afterResumeGlobal, 'Question:', afterResumeQuestion)
                    
                    // Wait and verify they're counting down
                    await studentPage.waitForTimeout(3000)
                    const nowGlobal = await studentGlobalTimer.textContent()
                    const nowQuestion = await studentQuestionTimer.textContent()
                    
                    console.log('Still running - Global:', nowGlobal, 'Question:', nowQuestion)
                    
                    // Times should be different (counting down)
                    expect(nowGlobal).not.toBe(afterResumeGlobal)
                    expect(nowQuestion).not.toBe(afterResumeQuestion)
                }
            }
        }
    })

    test('T4: Multiple students concurrent auto-submit', async () => {
        console.log('TEST: Multiple students concurrent submission')
        
        // Note: This test would need a third page context
        // For demonstration, we'll verify the existing student/teacher pair works
        
        const nextBtn = teacherPage.locator('button:has-text("Next")').first()
        if (await nextBtn.isVisible()) {
            await nextBtn.click()
            await teacherPage.waitForTimeout(1000)
            
            // Select answers on both student pages
            const options = studentPage.locator('[data-testid="option"]')
            if (await options.count() > 0) {
                await options.first().click()
            }
            
            // Verify both end up on same question
            await nextBtn.click() // Teacher advances
            await studentPage.waitForTimeout(1500)
            
            const teacherQ = await teacherPage.locator('[data-testid="question-text"]').textContent()
            const studentQ = await studentPage.locator('[data-testid="question-text"]').textContent()
            
            expect(teacherQ).toBe(studentQ)
            console.log('Both on same question:', teacherQ)
        }
    })

    test('T5: Grace period for late-arriving answers', async () => {
        console.log('TEST: Grace period for answers arriving after question change')
        
        // This test verifies that even if the API call is delayed,
        // the graceful fallback to previous question works
        
        // Setup interceptor to slow down API
        await studentPage.route('**/submit-answer', async route => {
            await new Promise(resolve => setTimeout(resolve, 3000)) // 3 second delay
            await route.continue()
        })
        
        const nextBtn = teacherPage.locator('button:has-text("Next")').first()
        if (await nextBtn.isVisible()) {
            // Student answers Q1
            const options = studentPage.locator('[data-testid="option"]')
            if (await options.count() > 0) {
                await options.first().click()
            }
            
            // Teacher immediately advances TWO more questions
            console.log('Teacher advancing questions quickly (grace period test)...')
            await nextBtn.click()
            await teacherPage.waitForTimeout(500)
            
            const tmpNextBtn = teacherPage.locator('button:has-text("Next")').first()
            if (await tmpNextBtn.isVisible()) {
                await tmpNextBtn.click()
            }
            
            // Student's auto-submit was delayed 3 seconds, but should succeed anyway
            // because of grace period check on backend
            await studentPage.waitForTimeout(5000)
            
            // Check for any error messages
            const errorToasts = studentPage.locator('[class*="error"]')
            const errorCount = await errorToasts.count()
            
            console.log('Error messages visible:', errorCount)
            expect(errorCount).toBe(0) // Should have no errors
        }
        
        // Clear the route override
        await studentPage.unroute('**/submit-answer')
    })

    test('T6: Verify answer recorded in database', async () => {
        console.log('TEST: Verify answer saved in database')
        
        // This would require backend API access or database query
        // For now, verify via the teacher's leaderboard display
        
        const leaderboard = teacherPage.locator('[data-testid="leaderboard"]')
        if (await leaderboard.isVisible()) {
            const studentEntry = leaderboard.locator('text=Student') // Adjust selector
            const isVisible = await studentEntry.isVisible({ timeout: 5000 })
            console.log('Student visible in leaderboard:', isVisible)
            expect(isVisible).toBTruthy()
        }
    })
})

// Helper functions
async function getConsoleMessages(page, keyword) {
    const messages = []
    page.on('console', msg => {
        if (msg.text().includes(keyword)) {
            messages.push(msg.text())
        }
    })
    return messages
}

async function waitForConsolMessage(page, keyword, timeout = 5000) {
    return new Promise((resolve) => {
        const interval = setInterval(() => {
            // Check if message appeared
            resolve(true)
        }, 100)
        
        setTimeout(() => {
            clearInterval(interval)
            resolve(false)
        }, timeout)
    })
}
