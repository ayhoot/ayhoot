<template>
    <div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 w-full gap-4">
            <!-- Left Column: Student view gets Doughnut + Play Again, Instructor view gets Exam Link + QR Code -->
            <div class="md:col-span-1 lg:col-span-3">
                <!-- STUDENT VIEW: Doughnut Chart & Play Again Card -->
                <div v-if="isStudent" class="flex flex-col sm:flex-row items-center bg-white gap-4 rounded-xl shadow-sm p-6 w-full h-full">
                    <div class="w-30 h-30 sm:w-36 sm:h-36 relative shrink-0">
                        <Doughnut :data="overallChartData" :options="chartOptions" />
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <div class="text-lg font-bold text-gray-800">{{ overallPercentage }}%</div>
                            <div class="text-sm font-bold text-gray-800">{{ t('reportTabs.summary.mainChart.correct') || 'correct' }}</div>
                        </div>
                    </div>
                    <div class="space-y-2 text-center sm:text-start">
                        <p class="text-xl font-semibold text-gray-800">
                            {{ feedbackMessage }}
                        </p>
                        <p v-if="!isAssignment" class="text-sm text-gray-500">
                            {{ t('reportTabs.summary.mainChart.feedbackDescription') || 'Play again and let the same group improve their score or see if new players can beat this result.' }}
                        </p>
                        <AyButton
                            v-if="!isAssignment"
                            type="light-green"
                            @click="playAgain"
                            class="text-sm px-4 py-2"
                        >
                            {{ t('reportTabs.summary.mainChart.playAgain') || 'Play Again' }}
                        </AyButton>
                    </div>
                </div>

                <!-- LIVE HOST VIEW: Leaderboard & Podium Card (No Link or QR Code) -->
                <div v-else-if="isLive" class="flex flex-col justify-between bg-white rounded-xl shadow-sm p-6 w-full h-full">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-200">
                        <div class="flex items-center gap-2">
                            <span class="p-1.5 rounded-lg bg-amber-100 text-amber-600">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M19 5h-2V3a1 1 0 00-1-1H8a1 1 0 00-1 1v2H5a3 3 0 00-3 3v1a5 5 0 004.9 5H8.1A6 6 0 0011 16.9V19H8a1 1 0 000 2h8a1 1 0 000-2h-3v-2.1a6 6 0 002.9-2.9h1.2A5 5 0 0022 9V8a3 3 0 00-3-3zM4 9V8a1 1 0 011-1h2v4.8A3 3 0 014 9zm16 0a3 3 0 01-3 2.8V7h2a1 1 0 011 1v1z" />
                                </svg>
                            </span>
                            <div>
                                <h3 class="font-bold text-gray-800 text-base">
                                    Top Performers & Leaderboard
                                </h3>
                                <p class="text-xs text-gray-500">Live quiz champions and final standings</p>
                            </div>
                        </div>
                        <span class="text-xs font-semibold text-purple-700 bg-purple-100 px-2.5 py-0.5 rounded-full border border-purple-200">
                            Live Host
                        </span>
                    </div>

                    <!-- Top 3 Podium Grid -->
                    <div v-if="topPlayers.length" class="grid grid-cols-3 gap-3 items-end pt-4 pb-2 my-auto">
                        <!-- 2nd Place -->
                        <div v-if="topPlayers[1]" class="bg-slate-50/90 rounded-2xl p-3 border border-slate-200 text-center order-1">
                            <div class="w-8 h-8 mx-auto mb-1.5 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center font-bold text-xs">
                                🥈 2
                            </div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">2nd Place</span>
                            <div class="flex items-center justify-center gap-1.5 mt-1.5">
                                <img
                                    :src="topPlayers[1].image || topPlayers[1].avatar || '/images/default-avatar.png'"
                                    class="w-7 h-7 rounded-full object-cover border border-gray-200 bg-white"
                                />
                                <p class="text-xs font-bold text-gray-900 truncate max-w-[10ch]">{{ topPlayers[1].name || topPlayers[1].nickname }}</p>
                            </div>
                            <p class="text-xs font-black text-blue-600 font-mono mt-1">{{ topPlayers[1].total_score ?? topPlayers[1].score ?? 0 }} pts</p>
                        </div>
                        <div v-else class="order-1"></div>

                        <!-- 1st Place (Champion) -->
                        <div v-if="topPlayers[0]" class="bg-gradient-to-b from-amber-50 to-white rounded-2xl p-4 border-2 border-amber-300 text-center shadow-xs order-2 -mt-2">
                            <div class="w-10 h-10 mx-auto mb-1.5 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center font-bold text-sm border-2 border-amber-300 shadow-xs">
                                👑 1
                            </div>
                            <span class="text-[11px] font-extrabold uppercase tracking-wider text-amber-600">Champion</span>
                            <div class="flex items-center justify-center gap-1.5 mt-1.5">
                                <img
                                    :src="topPlayers[0].image || topPlayers[0].avatar || '/images/default-avatar.png'"
                                    class="w-8 h-8 rounded-full object-cover border-2 border-amber-300 bg-white"
                                />
                                <p class="text-sm font-black text-gray-900 truncate max-w-[12ch]">{{ topPlayers[0].name || topPlayers[0].nickname }}</p>
                            </div>
                            <p class="text-sm font-black text-emerald-600 font-mono mt-1">{{ topPlayers[0].total_score ?? topPlayers[0].score ?? 0 }} pts</p>
                        </div>

                        <!-- 3rd Place -->
                        <div v-if="topPlayers[2]" class="bg-orange-50/70 rounded-2xl p-3 border border-orange-200 text-center order-3">
                            <div class="w-8 h-8 mx-auto mb-1.5 rounded-full bg-orange-200 text-orange-800 flex items-center justify-center font-bold text-xs">
                                🥉 3
                            </div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-orange-700">3rd Place</span>
                            <div class="flex items-center justify-center gap-1.5 mt-1.5">
                                <img
                                    :src="topPlayers[2].image || topPlayers[2].avatar || '/images/default-avatar.png'"
                                    class="w-7 h-7 rounded-full object-cover border border-gray-200 bg-white"
                                />
                                <p class="text-xs font-bold text-gray-900 truncate max-w-[10ch]">{{ topPlayers[2].name || topPlayers[2].nickname }}</p>
                            </div>
                            <p class="text-xs font-black text-blue-600 font-mono mt-1">{{ topPlayers[2].total_score ?? topPlayers[2].score ?? 0 }} pts</p>
                        </div>
                        <div v-else class="order-3"></div>
                    </div>

                    <div v-else class="text-center py-8 text-sm text-gray-500">
                        No participants data recorded yet.
                    </div>
                </div>

                <!-- INSTRUCTOR (TEACHER / INSTITUTE) VIEW: Exam Share Link & Downloadable QR Code Card -->
                <div v-else class="flex flex-col justify-between bg-white rounded-xl shadow-sm p-6 w-full h-full">
                    <!-- Top: Header Info (Icon & description removed, matches other cards style) -->
                    <div class="flex items-center justify-between pb-3 border-b border-gray-200">
                        <h3 class="font-semibold text-gray-800 text-base">
                            {{ t('reportTabs.summary.assignmentLink.title') || 'Assignment Link' }}
                        </h3>
                    </div>

                    <!-- Body: Input + Copy Button + QR Code -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-center pt-4 my-auto">
                        <!-- Link Input and Action Buttons (2 cols) -->
                        <div class="sm:col-span-2 flex flex-col gap-2.5">
                            <label class="text-xs font-semibold text-gray-600">
                                {{ t('reportTabs.summary.assignmentLink.linkTitle') || 'Game Link' }}
                            </label>
                            <div class="flex items-center gap-2">
                                <input
                                    type="text"
                                    readonly
                                    :value="examPlayUrl"
                                    dir="ltr"
                                    @click="$event.target.select()"
                                    class="flex-1 min-w-0 text-xs sm:text-sm text-gray-700 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 font-mono select-all focus:outline-none focus:ring-1 focus:ring-blue-500 focus:bg-white transition-all"
                                />
                                <AyButton
                                    :type="isCopied ? 'light-green' : 'dash-blue'"
                                    @click="copyLink"
                                    class="shrink-0 text-xs px-3 py-2 gap-1.5"
                                >
                                    <svg v-if="isCopied" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <svg v-else class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                                    </svg>
                                    <span>{{ isCopied ? (t('reportTabs.summary.assignmentLink.copied') || 'Copied!') : (t('reportTabs.summary.assignmentLink.copy') || 'Copy') }}</span>
                                </AyButton>
                            </div>
                            <div class="flex items-center gap-1.5 text-xs text-gray-500">
                                <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                                <span>{{ $i18n.locale === 'fa' ? 'دانشجویان با این لینک مستقیماً وارد آزمون می‌شوند' : 'Students can join directly with this link' }}</span>
                            </div>
                        </div>

                        <!-- QR Code & Download Button (1 col) -->
                        <div class="sm:col-span-1 flex flex-col items-center justify-center p-3 bg-gray-50 rounded-xl border border-gray-200 gap-2.5">
                            <div class="w-20 h-20 bg-white p-1 rounded-lg border border-gray-200 shadow-xs flex items-center justify-center">
                                <img
                                    v-if="qrCodeUrl"
                                    :src="qrCodeUrl"
                                    alt="Exam QR Code"
                                    class="w-full h-full object-contain cursor-pointer hover:opacity-90 transition-opacity"
                                    :title="t('reportTabs.summary.qrCode.download') || 'Click to download'"
                                    @click="downloadQrCode"
                                />
                                <div v-else class="w-5 h-5 border-2 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
                            </div>
                            <AyButton
                                type="light-gray"
                                @click="downloadQrCode"
                                class="w-full text-xs py-1.5 px-2 gap-1.5"
                            >
                                <svg class="w-3.5 h-3.5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                <span>{{ t('reportTabs.summary.qrCode.download') || 'Download QR' }}</span>
                            </AyButton>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Card -->
            <div class="md:col-span-1 lg:col-span-2">
                <div class="flex flex-col bg-white rounded-xl shadow-sm p-6 w-full h-full text-sm divide-y divide-gray-200">
                    <div class="flex-1 flex items-center justify-between py-2">
                        <div class="flex items-center gap-2">
                            <svg
                                width="20"
                                height="20"
                                viewBox="0 0 26 26"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    d="M2.3999 20.5124C2.3999 16.7369 5.55419 13.6762 11.9999 13.6762C18.4456 13.6762 21.5999 16.7369 21.5999 20.5124C21.5999 21.1131 21.1617 21.6 20.6211 21.6H3.37873C2.83814 21.6 2.3999 21.1131 2.3999 20.5124Z"
                                    fill="#A259FF"
                                />
                                <path
                                    d="M15.5999 6.00002C15.5999 7.98825 13.9881 9.60002 11.9999 9.60002C10.0117 9.60002 8.3999 7.98825 8.3999 6.00002C8.3999 4.0118 10.0117 2.40002 11.9999 2.40002C13.9881 2.40002 15.5999 4.0118 15.5999 6.00002Z"
                                    fill="#A259FF"
                                />
                                <path
                                    d="M2.3999 20.5124C2.3999 16.7369 5.55419 13.6762 11.9999 13.6762C18.4456 13.6762 21.5999 16.7369 21.5999 20.5124C21.5999 21.1131 21.1617 21.6 20.6211 21.6H3.37873C2.83814 21.6 2.3999 21.1131 2.3999 20.5124Z"
                                    stroke="#A259FF"
                                    stroke-width="2"
                                />
                                <path
                                    d="M15.5999 6.00002C15.5999 7.98825 13.9881 9.60002 11.9999 9.60002C10.0117 9.60002 8.3999 7.98825 8.3999 6.00002C8.3999 4.0118 10.0117 2.40002 11.9999 2.40002C13.9881 2.40002 15.5999 4.0118 15.5999 6.00002Z"
                                    stroke="#A259FF"
                                    stroke-width="2"
                                />
                            </svg>
                            <span class="font-semibold">{{ t('reportTabs.summary.stats.players') || 'Players' }}</span>
                        </div>
                        <span class="font-bold text-gray-800">{{ exam.players?.length || 0 }}</span>
                    </div>
                    <div class="flex-1 flex items-center justify-between py-2">
                        <div class="flex items-center gap-2">
                            <svg
                                width="20"
                                height="20"
                                viewBox="0 0 24 24"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    d="M17.4003 2.3999H6.60004C5.27457 2.3999 4.20005 3.47441 4.20004 4.79989L4.19995 19.1998C4.19994 20.5253 5.27446 21.5998 6.59994 21.5998L17.4 21.5999C18.7254 21.5999 19.8 20.5254 19.8 19.2L19.8003 4.79995C19.8003 3.47445 18.7258 2.3999 17.4 2.3999Z"
                                    fill="#105DFB"
                                />
                                <path
                                    d="M8.40033 7.1999H15.6003M8.40033 10.7999H15.6003M8.40033 14.3999H12.0003M6.60004 2.3999H17.4003C18.7258 2.3999 19.8003 3.47445 19.8003 4.79995L19.8 19.2C19.8 20.5254 18.7254 21.5999 17.4 21.5999L6.59994 21.5998C5.27446 21.5998 4.19994 20.5253 4.19995 19.1998L4.20004 4.79989C4.20005 3.47441 5.27457 2.3999 6.60004 2.3999Z"
                                    stroke="white"
                                    stroke-width="1.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                            <span class="font-semibold">{{ t('reportTabs.summary.stats.questions') || 'Questions' }}</span>
                        </div>
                        <span class="font-bold text-gray-800">{{ exam.questions?.length || 0 }}</span>
                    </div>
                    <div class="flex-1 flex items-center justify-between py-2">
                        <div class="flex items-center gap-2">
                            <svg
                                width="20"
                                height="20"
                                viewBox="0 0 25 24"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    d="M22 12C22 16.9706 17.9706 21 13 21C8.02944 21 4 16.9706 4 12C4 7.02944 8.02944 3 13 3C17.9706 3 22 7.02944 22 12Z"
                                    fill="#FF469D"
                                />
                                <path
                                    d="M16.1378 14.9615C16.5308 15.0925 16.9555 14.8801 17.0865 14.4872C17.2175 14.0942 17.0051 13.6695 16.6122 13.5385L16.1378 14.9615ZM13 13.125H12.25C12.25 13.4478 12.4566 13.7344 12.7628 13.8365L13 13.125ZM13.75 8.42087C13.75 8.00666 13.4142 7.67087 13 7.67087C12.5858 7.67087 12.25 8.00666 12.25 8.42087H13.75ZM16.6122 13.5385L13.2372 12.4135L12.7628 13.8365L16.1378 14.9615L16.6122 13.5385ZM13.75 13.125V8.42087H12.25V13.125H13.75ZM21.25 12C21.25 16.5563 17.5563 20.25 13 20.25V21.75C18.3848 21.75 22.75 17.3848 22.75 12H21.25ZM13 20.25C8.44365 20.25 4.75 16.5563 4.75 12H3.25C3.25 17.3848 7.61522 21.75 13 21.75V20.25ZM4.75 12C4.75 7.44365 8.44365 3.75 13 3.75V2.25C7.61522 2.25 3.25 6.61522 3.25 12H4.75ZM13 3.75C17.5563 3.75 21.25 7.44365 21.25 12H22.75C22.75 6.61522 18.3848 2.25 13 2.25V3.75Z"
                                    fill="white"
                                />
                            </svg>
                            <span class="font-semibold">{{ t('reportTabs.summary.stats.time') || 'Time' }}</span>
                        </div>
                        <span class="font-bold text-gray-800">{{ formatDuration(exam.duration) }}</span>
                    </div>
                    <div v-if="!isStudent" class="flex-1 flex items-center justify-between py-2">
                        <div class="flex items-center gap-2">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#42C287" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                            <span class="font-semibold">{{ t('reportTabs.summary.mainChart.correct') || 'Avg. Score' }}</span>
                        </div>
                        <span class="font-bold text-emerald-600">{{ overallPercentage }}%</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-7 w-full gap-4 py-4">
            <!-- Difficult questions -->
            <div
                class="lg:col-span-3 md:col-span-1 bg-white rounded-xl shadow-sm px-6 py-2 w-full relative max-h-96 overflow-y-auto"
            >
                <div
                    class="top-0 bg-white z-10 flex items-center justify-between border-b font-semibold border-gray-300"
                >
                    <h3 class="py-2">Difficult Questions ({{ exam.difficult_questions?.length || 0 }})</h3>
                    <div
                        class="tooltip tooltip-left"
                        data-tip="A question is difficult if less than 35% of participants answered correctly."
                    >
                        <button class="text-gray-400 hover:text-gray-600 cursor-pointer">
                            <svg
                                width="20"
                                height="20"
                                viewBox="0 0 24 24"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    d="M12 12V7.5M12 15.3354V15.375M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z"
                                    class="stroke-current"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </button>
                        <div class="tooltip-content">
                            A question is difficult if less than 35% of participants answered correctly.
                        </div>
                    </div>
                </div>
                <div
                    v-if="!exam.difficult_questions || exam.difficult_questions.length === 0"
                    class="text-center py-10 text-sm text-gray-500"
                >
                    No difficult questions.
                </div>
                <div v-for="question in exam.difficult_questions" :key="question.id" class="space-y-2 py-1">
                    <div
                        class="flex items-start gap-2 rounded-md px-4 py-2 border border-gray-100 cursor-pointer hover:shadow-sm"
                    >
                        <div class="flex flex-col space-y-2 justify-between w-full overflow-hidden">
                            <div>
                                <span class="text-xs text-gray-400">{{ question.order }} - </span>
                                <span class="text-xs text-gray-400 uppercase">{{
                                    question.type?.replace('_', ' ') || 'N/A'
                                }}</span>
                            </div>
                            <h3 class="font-semibold text-sm truncate" :title="question.title">
                                {{ question.title }}
                            </h3>
                            <div class="text-xs flex items-center gap-2">
                                <div class="w-5 h-5 shrink-0">
                                    <Doughnut :data="getChartDataFn(question)" :options="smallChartOptions" />
                                </div>
                                <span>
                                    {{ getCorrectPercentageFn(question) }}% correct in {{ question.avg_duration }}s avg
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Need Help tutees -->
            <div
                class="lg:col-span-2 md:col-span-1 bg-white rounded-xl shadow-sm px-6 py-2 w-full relative max-h-96 overflow-y-auto"
            >
                <div
                    class="sticky top-0 bg-white z-10 flex items-center justify-between border-b font-semibold border-gray-300"
                >
                    <h3 class="py-2">Need Help ({{ exam.help_needers?.length || 0 }})</h3>
                    <div
                        class="tooltip tooltip-left"
                        data-tip="Participants who answered less than 35% of questions correctly."
                    >
                        <button class="text-gray-400 hover:text-gray-600 cursor-pointer">
                            <svg
                                width="20"
                                height="20"
                                viewBox="0 0 24 24"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    d="M12 12V7.5M12 15.3354V15.375M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z"
                                    class="stroke-current"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </button>
                        <div class="tooltip-content">
                            Participants who answered less than 35% of questions correctly.
                        </div>
                    </div>
                </div>
                <div
                    v-if="!exam.help_needers || exam.help_needers.length === 0"
                    class="text-center py-10 text-sm text-gray-500 font-bold"
                >
                    No one seems to need help.
                </div>
                <div v-else>
                    <div
                        v-for="tutee in exam.help_needers"
                        :key="tutee.id"
                        class="py-2 border-b gap-2 flex items-center border-gray-200 text-sm"
                    >
                        <span
                            ><svg
                                width="16"
                                height="16"
                                viewBox="0 0 24 24"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    d="M3.23779 19.5C4.5632 17.2892 7.46807 15.7762 12.0001 15.7762C16.5321 15.7762 19.4369 17.2892 20.7623 19.5M15.6001 8.1C15.6001 10.0882 13.9883 11.7 12.0001 11.7C10.0118 11.7 8.40007 10.0882 8.40007 8.1C8.40007 6.11177 10.0118 4.5 12.0001 4.5C13.9883 4.5 15.6001 6.11177 15.6001 8.1Z"
                                    stroke="black"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                />
                            </svg>
                        </span>
                        {{ tutee?.name || 'Anonymous' }}
                    </div>
                </div>
            </div>

            <!-- Didn't finish -->
            <div
                class="lg:col-span-2 md:col-span-1 bg-white rounded-xl shadow-sm px-6 py-2 w-full relative max-h-96 overflow-y-auto"
            >
                <div
                    class="sticky top-0 bg-white z-10 flex items-center justify-between border-b font-semibold border-gray-300"
                >
                    <h3 class="py-2">Didn't Finish ({{ exam.not_finished?.length || 0 }})</h3>
                    <div class="tooltip tooltip-left" data-tip="Players with unfinished questions.">
                        <button class="text-gray-400 hover:text-gray-600 cursor-pointer">
                            <svg
                                width="20"
                                height="20"
                                viewBox="0 0 24 24"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    d="M12 12V7.5M12 15.3354V15.375M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z"
                                    class="stroke-current"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </button>
                        <div class="tooltip-content">Players with unfinished questions (e.g. timed out or left).</div>
                    </div>
                </div>
                <div
                    v-if="!exam.not_finished || exam.not_finished.length === 0"
                    class="text-center py-10 text-sm text-gray-500 font-bold"
                >
                    Everyone finished!
                </div>
                <div v-else>
                    <div
                        class="flex w-full items-center justify-between border-b border-gray-200 text-sm"
                        v-for="tutee in exam.not_finished"
                        :key="tutee.id"
                    >
                        <div class="flex items-center gap-2">
                            <span
                                ><svg
                                    width="16"
                                    height="16"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    xmlns="http://www.w3.org/2000/svg"
                                >
                                    <path
                                        d="M3.23779 19.5C4.5632 17.2892 7.46807 15.7762 12.0001 15.7762C16.5321 15.7762 19.4369 17.2892 20.7623 19.5M15.6001 8.1C15.6001 10.0882 13.9883 11.7 12.0001 11.7C10.0118 11.7 8.40007 10.0882 8.40007 8.1C8.40007 6.11177 10.0118 4.5 12.0001 4.5C13.9883 4.5 15.6001 6.11177 15.6001 8.1Z"
                                        stroke="black"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                    />
                                </svg>
                            </span>
                            <p class="py-2">
                                {{ tutee?.name || 'Anonymous' }}
                            </p>
                        </div>
                        <p class="py-2 text-gray-500">
                            {{ getUnansweredCount(tutee) }} <span class="text-xs">unanswered</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import AyButton from '@/components/atoms/button/AyButton.vue'
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'
import { Doughnut } from 'vue-chartjs'
import { Chart as ChartJS, Title, Tooltip, Legend, ArcElement, DoughnutController } from 'chart.js'
import QRCode from 'qrcode'
import { useI18n } from 'vue-i18n'
import { useToast } from 'vue-toast-notification'
import { getRole } from '@/utils/role'

ChartJS.register(Title, Tooltip, Legend, ArcElement, DoughnutController)

export default {
    name: 'SummaryTab',
    components: {
        AyButton,
        ResponsiveButton,
        Doughnut,
    },
    setup() {
        const { t, locale } = useI18n()
        const toast = useToast()
        return { t, locale, toast }
    },
    data() {
        return {
            isCopied: false,
            isCopying: false,
            isDownloading: false,
            qrCodeUrl: '',
        }
    },
    props: {
        isLive: {
            type: Boolean,
            default: false,
        },
        exam: {
            type: Object,
            required: true,
        },
        formatDuration: {
            // Passed from parent
            type: Function,
            required: true,
        },
        getChartDataFn: {
            // Passed from parent for consistency for difficult questions
            type: Function,
            required: true,
        },
        getCorrectPercentageFn: {
            // Passed from parent
            type: Function,
            required: true,
        },
    },
    computed: {
        topPlayers() {
            return [...(this.exam?.players || [])]
                .sort((a, b) => (b.total_score ?? b.score ?? 0) - (a.total_score ?? a.score ?? 0))
                .slice(0, 3)
        },
        isStudent() {
            if (this.$route && this.$route.path) {
                if (this.$route.path.startsWith('/student')) {
                    return true
                }
                if (this.$route.path.startsWith('/teacher') || this.$route.path.startsWith('/institute')) {
                    return false
                }
            }
            return getRole() === 'student'
        },
        isAssignment() {
            return Boolean(this.exam?.is_assignment || this.exam?.game_id || this.exam?.game)
        },
        examId() {
            return this.exam?.exam_id || this.exam?.exam?.id || this.exam?.id || this.$route?.params?.id
        },
        resolvedGameId() {
            if (this.exam?.game_id) return this.exam.game_id
            if (this.exam?.exam_id && this.exam?.id) return this.exam.id
            if (this.$route?.path?.includes('/report/assigned-ayhoots/') && this.$route?.params?.id) {
                return this.$route.params.id
            }
            return null
        },
        examPlayUrl() {
            const origin = typeof window !== 'undefined' ? window.location.origin : ''
            const url = `${origin}/student/exam/${this.examId}/play`
            return this.resolvedGameId ? `${url}?game=${this.resolvedGameId}` : url
        },
        overallPercentage() {
            return this.exam?.avg_score?.toFixed(1) || 0
        },
        overallChartData() {
            const percentage = parseFloat(this.overallPercentage)
            return {
                labels: ['Correct', 'Incorrect'],
                datasets: [
                    {
                        data: [percentage, 100 - percentage],
                        backgroundColor: ['#42C287', '#E5E7EB'],
                        borderWidth: 0,
                    },
                ],
            }
        },
        chartOptions() {
            return {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    tooltip: { enabled: false },
                    legend: { display: false },
                },
            }
        },
        smallChartOptions() {
            // For difficult question charts
            return {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%', // Smaller cutout for smaller charts
                plugins: {
                    tooltip: { enabled: false },
                    legend: { display: false },
                },
            }
        },
        feedbackMessage() {
            const p = parseFloat(this.overallPercentage)
            if (p > 75) return 'Well Done!'
            if (p > 50) return 'Well Played'
            if (p > 25) return 'You can do better'
            return 'Needs Improvement!'
        },
    },
    watch: {
        examPlayUrl: {
            immediate: true,
            handler() {
                if (!this.isStudent) {
                    this.generateQrCode()
                }
            },
        },
    },
    methods: {
        async generateQrCode() {
            if (!this.examPlayUrl) return
            try {
                this.qrCodeUrl = await QRCode.toDataURL(this.examPlayUrl, {
                    width: 250,
                    margin: 1,
                    color: {
                        dark: '#0f172a',
                        light: '#ffffff',
                    },
                })
            } catch (err) {
                console.error('Failed to generate QR code', err)
            }
        },
        async copyLink() {
            if (!this.examPlayUrl || this.isCopying) return
            this.isCopying = true
            try {
                await navigator.clipboard.writeText(this.examPlayUrl)
                this.isCopied = true
                this.toast.success(this.t('reportTabs.summary.assignmentLink.copied') || 'Link copied to clipboard!')
                setTimeout(() => {
                    this.isCopied = false
                    this.isCopying = false
                }, 2000)
            } catch (err) {
                // Fallback copy using textarea
                try {
                    const textarea = document.createElement('textarea')
                    textarea.value = this.examPlayUrl
                    textarea.style.position = 'fixed'
                    textarea.style.opacity = '0'
                    document.body.appendChild(textarea)
                    textarea.select()
                    document.execCommand('copy')
                    document.body.removeChild(textarea)
                    this.isCopied = true
                    this.toast.success(this.t('reportTabs.summary.assignmentLink.copied') || 'Link copied to clipboard!')
                    setTimeout(() => {
                        this.isCopied = false
                        this.isCopying = false
                    }, 2000)
                } catch (fallbackErr) {
                    console.error('Failed to copy', fallbackErr)
                    this.isCopying = false
                }
            }
        },
        async downloadQrCode() {
            if (!this.examPlayUrl || this.isDownloading) return
            this.isDownloading = true
            try {
                const highResUrl = await QRCode.toDataURL(this.examPlayUrl, {
                    width: 600,
                    margin: 2,
                    color: {
                        dark: '#000000',
                        light: '#ffffff',
                    },
                })
                const link = document.createElement('a')
                link.href = highResUrl
                link.download = `exam-${this.examId}-qr.png`
                document.body.appendChild(link)
                link.click()
                document.body.removeChild(link)
                this.toast.success(this.t('reportTabs.summary.qrCode.download') || 'QR code downloaded!')
            } catch (err) {
                console.error('Failed to download QR code', err)
                this.toast.error('Failed to download QR code')
            } finally {
                setTimeout(() => {
                    this.isDownloading = false
                }, 1000)
            }
        },
        playAgain() {
            if (this.examId) {
                this.$router.push(`/student/exam/${this.examId}/play`)
            } else {
                this.$router.push('/student/exam')
            }
        },
        getUnansweredCount(tutee) {
            return tutee?.unanswered_questions || 0
        },
    },
}
</script>

<style scoped>
/* Tooltip styles from parent are applied if not overridden or if this component is used standalone elsewhere */
.tooltip {
    position: relative;
    display: inline-block;
}

.tooltip .tooltip-content {
    visibility: hidden;
    width: max-content;
    max-width: 200px;
    background-color: #333;
    color: #fff;
    text-align: center;
    border-radius: 6px;
    padding: 5px 8px;
    position: absolute;
    z-index: 10;
    bottom: 125%;
    left: 50%;
    margin-left: -60px;
    opacity: 0;
    transition: opacity 0.3s;
    font-size: 0.75rem;
}

.tooltip:hover .tooltip-content {
    visibility: visible;
    opacity: 1;
}
.tooltip.tooltip-left .tooltip-content {
    bottom: auto;
    top: 50%;
    transform: translateY(-50%);
    left: auto;
    right: 125%;
    margin-left: 0;
    margin-right: 5px; /* Spacing from the button */
}
</style>
