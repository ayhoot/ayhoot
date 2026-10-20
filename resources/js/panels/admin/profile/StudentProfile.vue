<template>
    <div class="bg-gray-100 min-h-screen">
        <div class="fixed top-0 inset-x-0 z-50">
            <Header @toggle-sidebar="toggleSidebar" :teacherName="userName" :teacherUsername="userUsername" />
        </div>

        <div class="flex">
            <Sidebar :isOpen="isSidebarOpen" @close="isSidebarOpen = false" />

            <main class="bg-gray-100 ml-0 xl:ml-64 w-full overflow-y-auto pt-16 min-h-[calc(100vh-4rem)]">
                <div v-if="isLoading" class="flex items-center justify-center h-[calc(100vh-4rem)]">
                    <div class="animate-spin rounded-full h-16 w-16 border-t-4 border-b-4 border-indigo-500"></div>
                </div>

                <template v-else>
                    <div class="w-full relative">
                        <img :src="user.cover || '/imgHolders/bg3.jpg'" :alt="user.name + ' cover image'"
                            class="w-full lg:h-[30vh] h-[15vh] object-cover" />
                    </div>

                    <div class="w-full lg:px-12 px-6 -mt-12 sm:-mt-16 mb-8">
                        <div
                            class="flex flex-col lg:flex-row items-center lg:items-end justify-between bg-white p-4 sm:p-6 rounded-xl shadow-lg relative">
                            <div class="flex flex-col sm:flex-row items-center text-center sm:text-left">
                                <img :src="user.image || '/imgHolders/profile.svg'" :alt="user.name"
                                    class="w-24 h-24 sm:w-32 sm:h-32 rounded-full object-cover border-4 border-white shadow-md -mt-12 sm:-mt-16 lg:-mt-20 mb-2 sm:mb-0 sm:mr-6" />
                                <div class="sm:pt-2">
                                    <h3 class="text-xl sm:text-2xl font-bold text-gray-800">{{ user.name }}</h3>
                                     <p v-if="user.primaryInstitute.title" class="text-xs sm:text-sm text-gray-500">{{ user.primaryInstitute.title }}</p>
                                    <p v-else class="text-xs sm:text-sm text-gray-500">No Primary Institute</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-6 sm:gap-8 mt-4 lg:mt-0 text-gray-700 px-6">
                                <div class="text-center">
                                    <p class="text-lg sm:text-xl font-bold">{{ user.plays }}</p>
                                    <h3 class="text-xs sm:text-sm text-gray-500 uppercase">Plays</h3>
                                </div>
                                <div class="text-center">
                                    <p class="text-lg sm:text-xl font-bold">{{ user.enrollments }}</p>
                                    <h3 class="text-xs sm:text-sm text-gray-500 uppercase">Enrolls</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="px-4 sm:px-6 lg:px-12">
                        <div class="border-b border-gray-200">
                            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
                                <li class="mr-2">
                                    <button @click="activeTab = 'playedAyhoots'"
                                        :class="activeTab === 'playedAyhoots' ? 'text-blue-600 border-blue-600' : 'text-gray-500 border-transparent hover:text-gray-700 hover:border-gray-300'"
                                        class="inline-block p-4 border-b-2 rounded-t-lg font-semibold transition-colors duration-150 cursor-pointer">
                                        Played Ayhoots
                                    </button>
                                </li>
                                <li class="mr-2">
                                    <button @click="activeTab = 'enrolledCourses'"
                                        :class="activeTab === 'enrolledCourses' ? 'text-blue-600 border-blue-600' : 'text-gray-500 border-transparent hover:text-gray-700 hover:border-gray-300'"
                                        class="inline-block p-4 border-b-2 rounded-t-lg font-semibold transition-colors duration-150 cursor-pointer">
                                        Enrolled Courses
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="px-4 sm:px-6 lg:px-12 py-6">
                        <div v-show="activeTab === 'playedAyhoots'">
                            <DataTable v-if="user.playedAyhoots?.length > 0"
                                :columns="playedAyhootsColumns" :rows="user.playedAyhoots"
                                :wrapper-class="'overflow-x-auto bg-white rounded-xl shadow-sm'"
                                row-class="hover:bg-gray-50">
                                <template #cell:title="{ row }">
                                    <div>{{ row.title }}</div>
                                    <div class="text-xs text-gray-500">{{ row.date }}</div>
                                </template>
                                <template #cell:rank="{ row }">
                                    <span v-if="row.rank"
                                        :class="{
                                            'bg-yellow-100 text-yellow-800': row.rank === 1,
                                            'bg-gray-100 text-gray-800': row.rank === 2,
                                            'bg-orange-100 text-orange-800': row.rank === 3,
                                            'bg-gray-50 text-gray-600': row.rank > 3,
                                        }"
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                        #{{ row.rank }}
                                    </span>
                                     <span v-else class="text-gray-400">-</span>
                                </template>
                                <template #cell:score="{ row }">
                                    {{ row.correctAnswers }} / {{ row.totalQuestions }}
                                </template>
                                <template #cell:type="{ row }">
                                    <span :class="row.type === 'Assigned' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                        {{ row.type }}
                                    </span>
                                </template>
                            </DataTable>
                            <div v-else class="text-center py-12 bg-white rounded-xl shadow-sm">
                                <h3 class="text-lg font-medium text-gray-900">No Games Played Yet</h3>
                                <p class="mt-1 text-sm text-gray-500">This student hasn't completed any Ayhoots.</p>
                            </div>
                        </div>

                        <div v-show="activeTab === 'enrolledCourses'">
                            <DataTable v-if="user.enrolledCourses?.length > 0"
                                :columns="enrolledCoursesColumns" :rows="user.enrolledCourses"
                                :wrapper-class="'overflow-x-auto bg-white rounded-xl shadow-sm'"
                                row-class="hover:bg-gray-50" />
                            <div v-else class="text-center py-12 bg-white rounded-xl shadow-sm">
                                <h3 class="text-lg font-medium text-gray-900">No Courses Enrolled</h3>
                                <p class="mt-1 text-sm text-gray-500">This student has not enrolled in any courses.</p>
                            </div>
                        </div>
                    </div>
                </template>
            </main>
        </div>
    </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { apiCall } from '@/services/apiService'; 
import Sidebar from '@/components/layout/AdminSidebar.vue';
import Header from '@/components/layout/AdminHeader.vue';
import DataTable from '@/components/shared/DataTable.vue';

export default {
    name: 'StudentProfile',
    components: {
        Sidebar,
        Header,
        DataTable,
    },
    setup() {
        const route = useRoute();
        const activeTab = ref('playedAyhoots');
        const isSidebarOpen = ref(false);
        const isLoading = ref(true);
        
        const user = ref({
            name: '',
            username: '',
            image: '',
            cover: '',
            primaryInstitute: { id: null, title: '' },
            plays: 0,
            enrollments: 0,
            playedAyhoots: [],
            enrolledCourses: [],
        });

        const studentId = route.params.id;

        const playedAyhootsColumns = [
            { key: 'title', label: 'Ayhoot', align: 'left', thClass: 'font-medium', tdClass: 'font-medium text-gray-900' },
            { key: 'rank', label: 'Rank', align: 'left', thClass: 'font-medium', tdClass: 'text-gray-500' },
            { key: 'tutor', label: 'Tutor', align: 'left', thClass: 'font-medium' },
            { key: 'institute', label: 'Institute', align: 'left', thClass: 'font-medium', tdClass: 'text-gray-500' },
            { key: 'score', label: 'Score', align: 'left', thClass: 'font-medium', tdClass: 'text-gray-500 font-mono' },
            { key: 'type', label: 'Type', align: 'left', thClass: 'font-medium' },
        ];

        const enrolledCoursesColumns = [
            { key: 'title', label: 'Course Title', align: 'left', thClass: 'font-medium', tdClass: 'font-medium text-gray-900' },
            { key: 'creator', label: 'Course Creator', align: 'left', thClass: 'font-medium' },
            { key: 'enrollmentDate', label: 'Enrollment Date', align: 'left', thClass: 'font-medium', tdClass: 'text-gray-500' },
        ];

        const fetchStudentData = async () => {
            isLoading.value = true;
            try {
                const [profileRes, coursesRes] = await Promise.all([
                    apiCall('GET', `/api/admin/student/${studentId}`),
                    apiCall('GET', `/api/admin/student/${studentId}/get-course`)
                ]);

                const studentData = profileRes.student;
                const answersData = profileRes.answers;
                const coursesData = coursesRes.courses;

                user.value.name = studentData.name;
                user.value.username = studentData.username;
                user.value.image = studentData.avatar;
                user.value.cover = studentData.cover;
                
                if (studentData.institutes && studentData.institutes.length > 0) {
                    user.value.primaryInstitute = {
                        id: studentData.institutes[0].id,
                        title: studentData.institutes[0].name
                    };
                }

                user.value.plays = answersData.length;
                user.value.enrollments = coursesData.length;

                user.value.playedAyhoots = answersData.map(ans => ({
                    id: ans.id,
                    title: ans.exam?.title || 'Unknown Exam',
                    rank: ans.rank,
                    tutor: ans.exam?.teacher?.name || 'N/A',
                    institute: ans.exam?.institute?.name || 'N/A',
                    correctAnswers: ans.score_details.correct,
                    totalQuestions: ans.score_details.total,
                    type: ans.game_id ? 'Assigned' : 'Self-Played',
                    // FIX: Use string.substring() for reliable date formatting without external libraries
                    date: ans.completed_at ? ans.completed_at.substring(0, 10) : 'In Progress',
                }));

                user.value.enrolledCourses = coursesData.map(course => ({
                    id: course.id,
                    title: course.title,
                    creator: course.teacher?.name || course.institute?.name || 'N/A',
                    // FIX: Use string.substring() here as well
                    enrollmentDate: course.pivot.created_at ? course.pivot.created_at.substring(0, 10) : 'N/A',
                }));

            } catch (error) {
                console.error("Failed to fetch student data:", error);
                // Optionally, add user-facing error feedback here
            } finally {
                isLoading.value = false;
            }
        };

        onMounted(fetchStudentData);

        const userName = computed(() => user.value?.name || 'Student');
        const userUsername = computed(() => user.value?.username || 'student_user');


        const toggleSidebar = () => {
            isSidebarOpen.value = !isSidebarOpen.value;
        };

        return {
            activeTab,
            isSidebarOpen,
            isLoading,
            user,
            userName,
            userUsername,
            toggleSidebar,
            playedAyhootsColumns,
            enrolledCoursesColumns,
        };
    },
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #c4c4c4;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #a1a1a1;
}
</style>