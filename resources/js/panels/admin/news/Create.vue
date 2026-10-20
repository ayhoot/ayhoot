<template>
    <div class="bg-gray-50 min-h-screen">
        <!-- Sticky Header -->
        <div class="fixed top-0 inset-x-0 z-50">
            <Header @toggle-sidebar="toggleSidebar" :teacherName="teacherName" :teacherUsername="teacherUsername" />
        </div>
        <div class="flex">
            <Sidebar :isOpen="isSidebarOpen" @close="isSidebarOpen = false" />

            <main class="flex-1 bg-gray-50 ml-0 xl:ml-64 overflow-y-auto min-h-[calc(100vh-4rem)]">
                <!-- Loading State -->
                <div v-if="isLoading" class="text-center py-20">
                    <div class="inline-block animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600"></div>
                    <p class="mt-4 text-gray-600">Loading Article Editor...</p>
                </div>

                <div v-else>
                    <!-- Editor Header -->
                    <div
                        class="sticky top-16 mt-16 bg-gray-50/80 backdrop-blur-sm z-40 border-b border-gray-200 px-6 py-3 flex justify-between items-center"
                    >
                        <div>
                            <h2 class="text-xl font-bold">News Editor</h2>
                            <p class="text-sm text-gray-500">Create or edit your news article below.</p>
                        </div>
                        <div>
                            <ResponsiveButton @click="saveArticle" type="dash-blue" class="font-semibold px-5 py-2">
                                Save Changes
                            </ResponsiveButton>
                        </div>
                    </div>

                    <!-- Cover Image Section -->
                    <div class="p-6 md:p-8">
                        <div class="w-full mx-auto">
                            <h3 class="text-lg font-semibold mb-3 text-gray-700">Cover Image</h3>
                            <div class="p-4 border-2 border-gray-200 border-dashed rounded-lg text-center">
                                <img
                                    v-if="coverImageUrl"
                                    :src="coverImageUrl"
                                    class="max-w-md mx-auto inline-block rounded-md mb-4 shadow-md"
                                    alt="Cover Image Preview"
                                />
                                <div v-if="!coverImageUrl" class="py-10">
                                    <svg
                                        class="mx-auto h-12 w-12 text-gray-400"
                                        stroke="currentColor"
                                        fill="none"
                                        viewBox="0 0 48 48"
                                        aria-hidden="true"
                                    >
                                        <path
                                            d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        ></path>
                                    </svg>
                                    <p class="mt-2 text-sm text-gray-500">No cover image uploaded.</p>
                                </div>
                                <div class="mt-2">
                                    <label class="cursor-pointer text-blue-600 hover:underline font-semibold">
                                        {{ coverImageUrl ? 'Change Cover Image' : 'Upload a Cover Image' }}
                                        <input
                                            type="file"
                                            class="hidden"
                                            @change="handleCoverImageUpload"
                                            accept="image/*"
                                        />
                                    </label>
                                    <button
                                        v-if="coverImageUrl"
                                        @click="removeCoverImage"
                                        class="ml-4 text-red-600 hover:underline font-semibold"
                                    >
                                        Remove
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Editor Canvas -->
                    <div class="p-6 md:p-8 pt-0" @mouseup="handleSelectionChange" @keyup="handleSelectionChange">
                        <!-- Centered Content Column -->
                        <div class="max-w-3xl mx-auto">
                            <!-- Article Title Input -->
                            <input
                                type="text"
                                v-model="articleTitle"
                                placeholder="Article Title..."
                                class="text-4xl lg:text-5xl font-extrabold w-full focus:outline-none mb-12 text-gray-800 placeholder-gray-300"
                            />

                            <!-- Blocks Renderer -->
                            <template v-if="contentBlocks.length > 0">
                                <div
                                    v-for="(block, index) in contentBlocks"
                                    :key="block.id"
                                    class="group relative py-2"
                                >
                                    <div class="flex items-start gap-3">
                                        <BlockControls
                                            :index="index"
                                            :type="block.type"
                                            @delete="removeBlock"
                                            @change-type="changeBlockType"
                                            @align="setBlockAlignment"
                                            @set-level="setHeadingLevel"
                                        />
                                        <!-- Heading Block -->
                                        <component
                                            :is="`h${block.level || 1}`"
                                            v-if="block.type === 'heading'"
                                            contenteditable="true"
                                            @blur="updateBlockContent(index, $event.target.innerHTML)"
                                            class="w-full focus:outline-none focus:bg-gray-100 p-1 rounded font-bold"
                                            :class="{
                                                'text-4xl': block.level === 1,
                                                'text-3xl': block.level === 2,
                                                'text-2xl': block.level === 3,
                                                'text-left': !block.align || block.align === 'left',
                                                'text-center': block.align === 'center',
                                                'text-right': block.align === 'right',
                                            }"
                                            v-html="block.content"
                                        ></component>

                                        <!-- Paragraph Block -->
                                        <p
                                            v-else-if="block.type === 'paragraph'"
                                            contenteditable="true"
                                            @blur="updateBlockContent(index, $event.target.innerHTML)"
                                            class="text-base leading-relaxed w-full focus:outline-none focus:bg-gray-100 p-1 rounded"
                                            :class="{
                                                'text-left': !block.align || block.align === 'left',
                                                'text-center': block.align === 'center',
                                                'text-right': block.align === 'right',
                                            }"
                                            v-html="block.content"
                                        ></p>

                                        <!-- Image Block -->
                                        <div
                                            v-else-if="block.type === 'image'"
                                            class="w-full p-4 border-2 border-dashed rounded-lg"
                                            :class="{
                                                'flex justify-start': !block.align || block.align === 'left',
                                                'flex justify-center': block.align === 'center',
                                                'flex justify-end': block.align === 'right',
                                            }"
                                        >
                                            <div class="max-w-full text-center">
                                                <img
                                                    v-if="block.content.src"
                                                    :src="block.content.src"
                                                    class="max-w-full inline-block rounded-md mb-4 shadow-md"
                                                />
                                                <label
                                                    class="cursor-pointer text-blue-600 hover:underline font-semibold"
                                                >
                                                    {{ block.content.src ? 'Change Image' : 'Upload an Image' }}
                                                    <input
                                                        type="file"
                                                        class="hidden"
                                                        @change="handleImageUpload($event, index)"
                                                        accept="image/*"
                                                    />
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Add Block Button between blocks -->
                                    <AddBlockButton @add="addBlock(index + 1, $event)" />
                                </div>
                            </template>

                            <!-- Empty State Placeholder -->
                            <div v-else class="text-center py-16 border-2 border-gray-200 border-dashed rounded-lg">
                                <h3 class="text-gray-400 font-semibold">Your story starts here...</h3>
                                <p class="text-gray-400 text-sm mt-1">Click a button to add the first block.</p>
                                <div class="mt-6 flex items-center justify-center gap-4">
                                    <ResponsiveButton class="p-2" type="light-gray" @click="addBlock(0, 'paragraph')"
                                        >Add Paragraph</ResponsiveButton
                                    >
                                    <ResponsiveButton class="p-2" type="light-gray" @click="addBlock(0, 'heading')"
                                        >Add Heading</ResponsiveButton
                                    >
                                    <ResponsiveButton class="p-2" type="light-gray" @click="addBlock(0, 'image')"
                                        >Add Image</ResponsiveButton
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Floating Toolbar for Rich Text -->
                <FloatingToolbar
                    v-if="toolbar.show"
                    :style="{ top: toolbar.y + 'px', left: toolbar.x + 'px' }"
                    @exec-command="execCommand"
                />
            </main>
        </div>
    </div>
</template>
<script>
import Sidebar from '@/components/layout/AdminSidebar.vue'
import Header from '@/components/layout/AdminHeader.vue'
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'
import AddBlockButton from '@/components/news/AddBlockButton.vue'
import BlockControls from '@/components/news/BlockControls.vue'
import FloatingToolbar from '@/components/news/FloatingToolbar.vue'
import { apiCall } from '@/services/apiService'
import { useConfirm } from '@/composables/useConfirm'

const { confirm: confirmDialog, alert: alertDialog } = useConfirm()

export default {
    name: 'NewsCreate',
    components: { Sidebar, Header, AddBlockButton, BlockControls, FloatingToolbar, ResponsiveButton },
    data() {
        return {
            isSidebarOpen: false,
            isLoading: true,
            articleTitle: '',
            contentBlocks: [],
            nextBlockId: 0,
            toolbar: {
                show: false,
                x: 0,
                y: 0,
            },
            newsId: null, // To store the ID when editing an existing article
            coverImageUrl: null, // For previewing and showing existing image
            coverImageFile: null, // The actual file object for new uploads
            coverImageRemoved: false, // Flag to signal removal to the backend
        }
    },
    computed: {
        teacherName: () => JSON.parse(localStorage.getItem('user') || '{}').name || 'Admin',
        teacherUsername: () => JSON.parse(localStorage.getItem('user') || '{}').username || 'admin',
    },
    methods: {
        toggleSidebar() {
            this.isSidebarOpen = !this.isSidebarOpen
        },
        loadArticleData() {
            this.isLoading = true
            const id = this.$route.params.id
            if (id) {
                this.newsId = id
                apiCall('GET', `/api/admin/news/${id}`)
                    .then((response) => {
                        this.articleTitle = response.news.title
                        this.coverImageUrl = response.news.cover_image_url || null
                        this.contentBlocks = JSON.parse(response.news.description || '[]')
                        // Ensure nextBlockId is higher than any existing ID
                        this.nextBlockId =
                            this.contentBlocks.reduce((maxId, block) => Math.max(block.id, maxId), -1) + 1
                        this.isLoading = false
                    })
                    .catch((err) => {
                        console.error(err)
                        this.isLoading = false
                    })
            } else {
                // Start with an empty slate for a new article
                this.articleTitle = ''
                this.contentBlocks = []
                this.isLoading = false
            }
        },
        createBlock(type) {
            const newBlock = {
                id: this.nextBlockId++,
                type,
                content: '',
                align: 'left',
            }
            if (type === 'heading') {
                newBlock.level = 2 // Default to H2
                newBlock.content = 'New Heading'
            }
            if (type === 'paragraph') {
                newBlock.content = 'New paragraph. Start writing here...'
            }
            if (type === 'image') {
                newBlock.content = { src: '', alt: '' }
            }
            return newBlock
        },
        addBlock(index, type) {
            const newBlock = this.createBlock(type)
            this.contentBlocks.splice(index, 0, newBlock)
        },
        removeBlock(index) {
            this.contentBlocks.splice(index, 1)
        },
        updateBlockContent(index, newContent) {
            if (this.contentBlocks[index]) {
                this.contentBlocks[index].content = newContent
            }
        },
        changeBlockType({ index, type }) {
            const newBlock = this.createBlock(type)
            if (typeof this.contentBlocks[index].content === 'string') {
                // Strip HTML for cleaner transition
                const tempDiv = document.createElement('div')
                tempDiv.innerHTML = this.contentBlocks[index].content
                newBlock.content = tempDiv.textContent || tempDiv.innerText || ''
            }
            this.contentBlocks.splice(index, 1, newBlock)
        },
        handleCoverImageUpload(event) {
            const file = event.target.files[0]
            if (!file) return

            this.coverImageFile = file
            this.coverImageRemoved = false

            const reader = new FileReader()
            reader.onload = (e) => {
                this.coverImageUrl = e.target.result
            }
            reader.readAsDataURL(file)
        },
        removeCoverImage() {
            this.coverImageUrl = null
            this.coverImageFile = null
            this.coverImageRemoved = true
        },
        handleImageUpload(event, index) {
            const file = event.target.files[0]
            if (file) {
                const reader = new FileReader()
                reader.onload = (e) => {
                    this.contentBlocks[index].content.src = e.target.result
                }
                reader.readAsDataURL(file)
            }
        },
        setBlockAlignment({ index, direction }) {
            this.contentBlocks[index].align = direction
        },
        setHeadingLevel({ index, level }) {
            this.contentBlocks[index].level = level
        },
        handleSelectionChange() {
            const selection = window.getSelection()
            if (!selection || selection.isCollapsed) {
                this.toolbar.show = false
                return
            }

            const range = selection.getRangeAt(0)
            const rect = range.getBoundingClientRect()

            let parent = range.startContainer.parentElement
            let isEditable = false
            while (parent) {
                if (parent.hasAttribute('contenteditable')) {
                    isEditable = true
                    break
                }
                parent = parent.parentElement
            }

            if (isEditable) {
                this.toolbar.x = rect.left + window.scrollX + rect.width / 2
                this.toolbar.y = rect.top + window.scrollY - 50 // Position above selection
                this.toolbar.show = true
            } else {
                this.toolbar.show = false
            }
        },
        execCommand(command, value = null) {
            document.execCommand(command, false, value)
            this.toolbar.show = false
        },
        async saveArticle() {
            // Use FormData to handle file uploads along with other data
            const formData = new FormData()
            formData.append('title', this.articleTitle)
            formData.append('description', JSON.stringify(this.contentBlocks))
            formData.append('status', 'published') // Or make this dynamic

            // Append cover image file if it exists
            if (this.coverImageFile) {
                // CHANGED: The field name is now 'image' as requested
                formData.append('image', this.coverImageFile)
            }

            // Signal to the backend if the cover image should be removed
            if (this.coverImageRemoved) {
                formData.append('remove_cover_image', '1')
            }

            try {
                if (this.newsId) {
                    // When updating, Laravel expects a _method field if using POST to simulate PUT/PATCH
                    // However, we send a POST directly as per the original logic for updates.
                    await apiCall('POST', `/api/admin/news/${this.newsId}`, formData)
                } else {
                    await apiCall('POST', '/api/admin/news', formData)
                }
                await alertDialog({ message: 'Article saved successfully!' })
                this.$router.push('/admin/news')
            } catch (error) {
                await alertDialog({ message: 'Failed to save article. Please check the console.' })
                console.error('Save failed:', error)
            }
        },
    },
    mounted() {
        this.loadArticleData()
    },
}
</script>
<style>
/* Add some subtle transitions for a smoother experience */
.group .transition-opacity {
    transition-property: opacity;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 200ms;
}
</style>
