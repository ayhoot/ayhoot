<template>
    <div>
        <div class="fixed inset-0 flex flex-col items-center justify-center bg-gray-100 overflow-hidden">
            <div
                class="absolute w-100 h-100 bg-gray-300 rounded-full opacity-30"
                style="top: 10%; left: -10%; transform: rotate(25deg)"
            ></div>
            <div
                class="absolute w-100 h-100 bg-gray-300 rounded-lg opacity-20"
                style="top: 40%; right: -10%; transform: rotate(-15deg)"
            ></div>
            <LoginNavBar />
            <div class="bg-white p-8 rounded-2xl shadow-sm w-full max-w-sm z-50">
                <h1 class="text-2xl font-bold text-center mb-6">Admin Login</h1>
                <form @submit.prevent="handleSubmit">
                    <div class="py-1">
                        <label for="username" class="block text-xs font-bold">Username</label>
                        <input
                            type="text"
                            id="username"
                            v-model="username"
                            placeholder="Username"
                            class="mt-1 block w-full px-3 py-2 border border-black rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        />
                    </div>
                    <div class="py-1 relative">
                        <label for="password" class="block text-xs font-bold">Password</label>
                        <input
                            :type="showPassword ? 'text' : 'password'"
                            id="password"
                            v-model="password"
                            placeholder="Password"
                            class="mt-1 block w-full px-3 py-2 border border-black rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        />
                        <button
                            type="button"
                            @click="togglePassword"
                            class="absolute right-3 top-8 transform text-gray-500"
                        >
                            <svg
                                v-if="showPassword"
                                width="20"
                                height="20"
                                viewBox="0 0 24 24"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <g id="Iconly/Curved/Light/Show">
                                    <g id="Show">
                                        <path
                                            id="Stroke 1"
                                            fill-rule="evenodd"
                                            clip-rule="evenodd"
                                            d="M15.1643 12.0523C15.1643 13.7983 13.7483 15.2143 12.0023 15.2143C10.2563 15.2143 8.84033 13.7983 8.84033 12.0523C8.84033 10.3053 10.2563 8.89026 12.0023 8.89026C13.7483 8.89026 15.1643 10.3053 15.1643 12.0523Z"
                                            stroke="black"
                                            stroke-width="1.5"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                        <path
                                            id="Stroke 3"
                                            fill-rule="evenodd"
                                            clip-rule="evenodd"
                                            d="M2.75024 12.0522C2.75024 15.3322 6.89224 19.3542 12.0022 19.3542C17.1112 19.3542 21.2542 15.3352 21.2542 12.0522C21.2542 8.76924 17.1112 4.75024 12.0022 4.75024C6.89224 4.75024 2.75024 8.77224 2.75024 12.0522Z"
                                            stroke="black"
                                            stroke-width="1.5"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </g>
                                </g>
                            </svg>
                            <svg
                                v-else
                                width="20"
                                height="20"
                                viewBox="0 0 24 24"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <g id="Iconly/Curved/Light/Hide">
                                    <g id="Hide">
                                        <path
                                            id="Stroke 1"
                                            d="M6.42 17.7299C4.19 16.2699 2.75 14.0699 2.75 12.1399C2.75 8.85991 6.89 4.8399 12 4.8399C14.09 4.8399 16.03 5.5099 17.59 6.5499"
                                            stroke="black"
                                            stroke-width="1.5"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                        <path
                                            id="Stroke 3"
                                            d="M19.8496 8.61029C20.7406 9.74029 21.2596 10.9903 21.2596 12.1403C21.2596 15.4203 17.1096 19.4403 11.9996 19.4403C11.0896 19.4403 10.2006 19.3103 9.36963 19.0803"
                                            stroke="black"
                                            stroke-width="1.5"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                        <path
                                            id="Stroke 5"
                                            d="M9.7656 14.3671C9.1706 13.7781 8.8376 12.9751 8.8406 12.1381C8.8366 10.3931 10.2486 8.97512 11.9946 8.97212C12.8346 8.97012 13.6406 9.30312 14.2346 9.89712"
                                            stroke="black"
                                            stroke-width="1.5"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                        <path
                                            id="Stroke 7"
                                            d="M15.1095 12.6992C14.8755 13.9912 13.8645 15.0042 12.5725 15.2412"
                                            stroke="black"
                                            stroke-width="1.5"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                        <path
                                            id="Stroke 9"
                                            d="M19.8917 4.25L4.11768 20.024"
                                            stroke="black"
                                            stroke-width="1.5"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </g>
                                </g>
                            </svg>
                        </button>
                    </div>
                    <div class="flex-1 py-2 mt-4 border-t border-gray-300"></div>
                    <AyButton class="py-2 w-full mt-2" type="dark-blue"> Login </AyButton>
                    <div class="flex w-full items-center justify-center pt-4">
                        <RouterLink to="/login" class="text-sm font-semibold text-gray-500"
                            >Login to User Dashboard Instead</RouterLink
                        >
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
import { apiCall } from '@/services/apiService'
import { RouterLink } from 'vue-router'
import GoogleAccount from '@/components/atoms/icons/GoogleAccount.vue'
import NavDropdown from '@/components/molecules/dropdown/NavDropdown.vue'
import AyButton from '@/components/atoms/button/AyButton.vue'
import LoginNavBar from '@/components/organisms/nav/LoginNavBar.vue'

export default {
    name: 'AdminLogin',
    components: { GoogleAccount, NavDropdown, AyButton, LoginNavBar, RouterLink },
    data() {
        return {
            username: '',
            password: '',
            showPassword: false,
        }
    },
    methods: {
        async handleSubmit() {
            const response = await apiCall('POST', '/api/login/admin', {
                username: this.username,
                password: this.password,
            })
            localStorage.setItem('token', response.token)
            localStorage.setItem('user', JSON.stringify(response.user))
            // Must store role too, or the router guard treats the session as stale
            // and bounces back to /admin/login. (The main login already does this.)
            localStorage.setItem('role', response.role)
            this.$router.push('/' + response.role + '/dashboard')
        },
        loginWithGoogle() {
            window.location.href = '/api/auth/google'
        },
        togglePassword() {
            this.showPassword = !this.showPassword
        },
    },
}
</script>
