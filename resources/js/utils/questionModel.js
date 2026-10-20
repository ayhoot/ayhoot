/**
 * Pure model/logic for the question create/edit form.
 *
 * Everything here is framework-agnostic (no `this`, no component state) so it can be
 * unit-tested and reused. The Vue component (QuestionCreateForm.vue) holds only UI state
 * and orchestration; the shape of a question, how it is parsed from the API, how its
 * payload is built, and how it is validated all live here.
 */

export const QUESTION_TYPES = {
    MULTIPLE_CHOICE: 'multiple_choice',
    TRUE_FALSE: 'true_false',
    ORDINARY: 'ordinary',
    SEQUENCE: 'sequence',
    SLIDE: 'slide',
}

export const SHAPE_MAP = [
    { name: 'triangle', color: 'red', icon: 'triangle' },
    { name: 'diamond', color: 'blue', icon: 'diamond' },
    { name: 'circle', color: 'yellow', icon: 'circle' },
    { name: 'rectangle', color: 'green', icon: 'rectangle' },
    { name: 'pentagon', color: 'pink', icon: 'pentagon' },
    { name: 'hexagon', color: 'purple', icon: 'hexagon' },
]

export const DEFAULT_BACKGROUND = '/imgHolders/blue.jpg'

export const BACKGROUND_OPTIONS = [
    '/imgHolders/blue.jpg',
    '/imgHolders/green.jpg',
    '/imgHolders/orange.jpg',
    '/imgHolders/pink.jpg',
    '/imgHolders/purple.jpg',
    '/imgHolders/yellow.jpg',
]

export const TOOLBAR_OPTIONS = [
    [{ header: [1, 2, 3, 4, 5, 6, false] }],
    ['bold', 'italic', 'underline', 'strike'],
    ['blockquote', 'code-block'],
    [{ list: 'ordered' }, { list: 'bullet' }],
    [{ script: 'sub' }, { script: 'super' }],
    [{ indent: '-1' }, { indent: '+1' }],
    [{ direction: 'rtl' }],
    [{ color: [] }, { background: [] }],
    [{ align: [] }],
    ['clean'],
    ['link', 'image', 'video'],
]

export function getDefaultQuestion() {
    return {
        questionType: QUESTION_TYPES.MULTIPLE_CHOICE,
        title: '',
        duration: 20,
        points: 5,
        previewImage: null,
        selectedFile: null,
        bg: DEFAULT_BACKGROUND,
        correctAnswer: 'true',
        options: [],
        correctOption: null,
        shortAnswer: '',
        alternativeAnswers: [],
        caseSensitive: false,
        sequenceOptions: [],
        allowPartialCredit: true,
        slide: '',
        explanation: '',
        difficultyLevel: 'medium',
        category: '',
        files: [],
        originalMainImageFile: null,
    }
}

export function createOption(index = 0) {
    const shape = SHAPE_MAP[index % SHAPE_MAP.length]
    return {
        text: '',
        id: String.fromCharCode(65 + index),
        shape: `${shape.color}${shape.name}`,
        image: null,
        imagePreview: null,
        file: null,
        correct: false,
        fieldId: Math.random().toString(36).substr(2, 9),
    }
}

export function createSequenceItem(index = 0) {
    const shape = SHAPE_MAP[index % SHAPE_MAP.length]
    return {
        text: '',
        id: String.fromCharCode(65 + index),
        shape: `${shape.color}${shape.name}`,
        order: index,
        fieldId: Math.random().toString(36).substr(2, 9),
    }
}

export function stripHtml(html) {
    if (!html) return ''
    const doc = new DOMParser().parseFromString(html, 'text/html')
    return doc.body.textContent || ''
}

export function getShapeColor(shapeName) {
    if (!shapeName) return 'bg-gray-300'
    const shape = SHAPE_MAP.find((s) => shapeName.toLowerCase().includes(s.color))
    return shape ? `bg-${shape.color}-500` : 'bg-gray-300'
}

export function getShapeIcon(shapeName) {
    if (!shapeName) return ''
    const shape = SHAPE_MAP.find((s) => shapeName.toLowerCase().includes(s.name))
    return shape?.icon || ''
}

export function getOptionImageUrl(imagePath) {
    if (!imagePath) return null
    if (imagePath.startsWith('data:') || imagePath.startsWith('http')) return imagePath
    if (imagePath.startsWith('/uploads/')) return imagePath

    const cleanPath = imagePath.replace(/\\/g, '/')

    if (cleanPath.startsWith('uploads/options/') || cleanPath.startsWith('uploads/questions/')) {
        return `/${cleanPath}`
    }
    if ((cleanPath.startsWith('option_') || cleanPath.startsWith('tf_option_')) && !cleanPath.startsWith('options/')) {
        return `/uploads/options/${cleanPath}`
    }
    if (cleanPath.startsWith('options/')) {
        return `/uploads/${cleanPath}`
    }
    return `/uploads/${cleanPath.replace(/^\//, '')}`
}

/** Translated badge label for a question type. Pass the i18n `t` function. */
export function getBadgeLabel(type, t) {
    const labels = {
        [QUESTION_TYPES.MULTIPLE_CHOICE]: t('createQuestion.questionTypes.quiz'),
        [QUESTION_TYPES.TRUE_FALSE]: t('createQuestion.questionTypes.trueOrFalse'),
        [QUESTION_TYPES.ORDINARY]: t('createQuestion.questionTypes.shortAnsBadge'),
        [QUESTION_TYPES.SLIDE]: t('createQuestion.questionTypes.slide'),
        [QUESTION_TYPES.SEQUENCE]: t('createQuestion.questionTypes.puzzle'),
    }
    return labels[type] || 'Unknown'
}

export function getBadgeStyle(type) {
    const styles = {
        [QUESTION_TYPES.MULTIPLE_CHOICE]: 'bg-red-100 text-red-400 border-red-400',
        [QUESTION_TYPES.TRUE_FALSE]: 'bg-blue-100 text-blue-400 border-blue-400',
        [QUESTION_TYPES.ORDINARY]: 'bg-purple-100 text-purple-400 border-purple-400',
        [QUESTION_TYPES.SLIDE]: 'bg-gray-100 text-gray-400 border-gray-400',
        [QUESTION_TYPES.SEQUENCE]: 'bg-yellow-100 text-yellow-400 border-yellow-400',
    }
    return styles[type] || 'bg-gray-100 text-gray-400 border-gray-400'
}

/**
 * Convert an API question record into the local editor state object.
 * Mirrors the previous inline parsing in loadQuestion().
 */
export function parseApiQuestion(apiQuestion) {
    const q = getDefaultQuestion()
    q.id = apiQuestion.id
    q.title = apiQuestion.title
    q.questionType = apiQuestion.type
    q.duration = apiQuestion.duration
    q.points = apiQuestion.points
    q.explanation = apiQuestion.explanation || ''
    q.difficultyLevel = apiQuestion.difficulty_level || 'medium'
    q.category = apiQuestion.category_id || ''
    q.slide = apiQuestion.slide || ''

    // Main image
    if (apiQuestion.files && Array.isArray(apiQuestion.files)) {
        if (apiQuestion.files.length > 0) {
            q.previewImage = '/uploads/questions/' + apiQuestion.files[0]
            q.originalMainImageFile = '/uploads/questions/' + apiQuestion.files[0]
            q.selectedFile = null
        }
    } else if (apiQuestion.files && typeof apiQuestion.files === 'string') {
        try {
            const filesArray = JSON.parse(apiQuestion.files)
            if (filesArray.length > 0) {
                q.previewImage = '/uploads/questions/' + filesArray[0]
                q.originalMainImageFile = '/uploads/questions/' + filesArray[0]
                q.selectedFile = null
            }
        } catch (e) {
            console.error('Error parsing main files:', e)
        }
    }

    // Background from q_options
    if (apiQuestion.q_options) {
        try {
            const qOpts = typeof apiQuestion.q_options === 'string' ? JSON.parse(apiQuestion.q_options) : apiQuestion.q_options
            q.bg = qOpts.background_image || DEFAULT_BACKGROUND
        } catch (e) {
            console.error('Error parsing q_options:', e)
            q.bg = DEFAULT_BACKGROUND
        }
    } else {
        q.bg = DEFAULT_BACKGROUND
    }

    // Type-specific
    if (apiQuestion.type === QUESTION_TYPES.TRUE_FALSE) {
        let opts = []
        if (Array.isArray(apiQuestion.options)) {
            opts = apiQuestion.options
        } else if (typeof apiQuestion.options === 'string') {
            try {
                opts = JSON.parse(apiQuestion.options || '[]')
            } catch (e) {
                console.error('Error parsing TF options:', e)
                opts = []
            }
        }
        const trueOpt = opts.find((o) => o.text === 'True')
        q.correctAnswer = trueOpt && trueOpt.correct ? 'true' : 'false'
    } else if (apiQuestion.type === QUESTION_TYPES.MULTIPLE_CHOICE) {
        try {
            let opts = []
            if (Array.isArray(apiQuestion.options)) {
                opts = apiQuestion.options
            } else if (typeof apiQuestion.options === 'string') {
                opts = JSON.parse(apiQuestion.options || '[]')
            }
            q.options = opts.map((opt) => ({
                ...opt,
                imagePreview: opt.image
                    ? opt.image.startsWith('data:')
                        ? opt.image
                        : getOptionImageUrl(opt.image)
                    : null,
                file: null,
                fieldId: opt.id || Math.random().toString(36).substr(2, 9),
            }))
            const correctIdx = q.options.findIndex((o) => o.correct)
            q.correctOption = correctIdx !== -1 ? correctIdx : null
        } catch (e) {
            console.error('Error parsing MC options:', e)
            q.options = []
        }
    } else if (apiQuestion.type === QUESTION_TYPES.ORDINARY) {
        q.shortAnswer = apiQuestion.short_answer || ''
        q.alternativeAnswers = Array.isArray(apiQuestion.alternative_answers)
            ? apiQuestion.alternative_answers
            : apiQuestion.alternative_answers
                ? JSON.parse(apiQuestion.alternative_answers || '[]')
                : []
        q.caseSensitive = !!apiQuestion.case_sensitive
    } else if (apiQuestion.type === QUESTION_TYPES.SEQUENCE) {
        try {
            let seqOpts = []
            if (Array.isArray(apiQuestion.sequence_options)) {
                seqOpts = apiQuestion.sequence_options
            } else if (Array.isArray(apiQuestion.options)) {
                seqOpts = apiQuestion.options
            } else if (typeof apiQuestion.options === 'string') {
                seqOpts = JSON.parse(apiQuestion.options || '[]')
            }
            q.sequenceOptions = seqOpts.map((opt) => ({
                ...opt,
                fieldId: opt.id || Math.random().toString(36).substr(2, 9),
            }))
            q.allowPartialCredit = !!apiQuestion.allow_partial_credit
        } catch (e) {
            console.error('Error parsing Sequence options:', e)
            q.sequenceOptions = []
        }
    }

    return q
}

/**
 * Build the multipart FormData payload for create/update.
 * Mirrors the previous inline preparePayload().
 */
export function buildQuestionFormData(question, examId) {
    const formData = new FormData()
    formData.append('title', question.title)
    formData.append('exam_id', examId)
    formData.append('type', question.questionType)
    formData.append('duration', question.duration)
    formData.append('points', question.points)
    formData.append('difficulty_level', question.difficultyLevel)
    formData.append('explanation', question.explanation)
    formData.append('category_id', question.category)
    formData.append('slide', question.slide)

    formData.append('q_options', JSON.stringify({ background_image: question.bg }))

    if (question.selectedFile) {
        formData.append('files[0]', question.selectedFile)
    } else if (question.previewImage === null && question.originalMainImageFile) {
        formData.append('remove_main_image', 'true')
    }

    switch (question.questionType) {
        case QUESTION_TYPES.TRUE_FALSE:
            formData.append(
                'options',
                JSON.stringify([
                    { text: 'True', correct: question.correctAnswer === 'true', shape: 'greenrectangle' },
                    { text: 'False', correct: question.correctAnswer === 'false', shape: 'redtriangle' },
                ])
            )
            formData.append('correct_answer', question.correctAnswer)
            break

        case QUESTION_TYPES.MULTIPLE_CHOICE: {
            const correctOptIndex = typeof question.correctOption === 'number' ? question.correctOption : -1
            const mcOptions = question.options.map((opt, index) => {
                const optionData = {
                    text: opt.text,
                    id: opt.id,
                    shape: opt.shape,
                    correct: index === correctOptIndex,
                    fieldId: opt.fieldId,
                }
                if (opt.file && opt.file instanceof File) {
                    const fileName = `option_${opt.id}_image.${opt.file.name.split('.').pop()}`
                    formData.append('option_files[]', opt.file)
                    optionData.image = fileName
                } else if (opt.imagePreview && opt.imagePreview.startsWith('/uploads/')) {
                    optionData.image = opt.imagePreview.replace('/uploads/', '')
                } else if (opt.image && !opt.image.startsWith('/uploads/')) {
                    optionData.image = opt.image
                } else {
                    optionData.image = null
                }
                return optionData
            })
            formData.append('options', JSON.stringify(mcOptions))
            formData.append('correct_option', correctOptIndex !== -1 ? String.fromCharCode(65 + correctOptIndex) : '')
            break
        }

        case QUESTION_TYPES.ORDINARY:
            formData.append('short_answer', question.shortAnswer)
            formData.append('alternative_answers', JSON.stringify(question.alternativeAnswers || []))
            formData.append('case_sensitive', question.caseSensitive ? '1' : '0')
            break

        case QUESTION_TYPES.SEQUENCE: {
            const seqOptions = question.sequenceOptions.map((opt) => ({
                text: opt.text,
                id: opt.id,
                shape: opt.shape,
                order: opt.order,
                fieldId: opt.fieldId,
            }))
            formData.append('options', JSON.stringify(seqOptions))
            formData.append('allow_partial_credit', question.allowPartialCredit ? '1' : '0')
            break
        }
    }

    return formData
}

/**
 * Validate a question before submit.
 * Returns null when valid, otherwise a translated error message. Pass the i18n `t`.
 */
export function validateQuestion(question, t) {
    if (question.questionType !== QUESTION_TYPES.SLIDE && !stripHtml(question.title).trim()) {
        return t('createQuestion.alerts.titleEmpty')
    }
    if (question.questionType === QUESTION_TYPES.MULTIPLE_CHOICE) {
        if (question.options.length < 2) return t('createQuestion.alerts.mcMinOptions')
        if (question.options.some((opt) => !opt.text.trim())) return t('createQuestion.alerts.mcEmptyOptionText')
        if (
            question.correctOption === null ||
            question.correctOption < 0 ||
            question.correctOption >= question.options.length
        ) {
            return t('createQuestion.alerts.mcNoCorrectAnswer')
        }
    }
    return null
}
