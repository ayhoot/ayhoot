<template>
  <InstructorLayout :secondary="false">
        <div class="max-w-4xl mx-auto">
          <div class="bg-white rounded-lg shadow-sm p-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">{{ $t('editCourse.title') }}</h1>
            
            <form @submit.prevent="handleSubmit" class="space-y-6">
              <!-- Course Title -->
              <div>
                <AyInput
                  v-model="course.title"
                  type="text"
                  :label="$t('editCourse.courseTitle')"
                  :placeholder="$t('editCourse.courseTitlePlaceholder')"
                  required
                />
              </div>

              <!-- Course Description -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  {{ $t('editCourse.courseDescription') }}
                </label>
                <textarea
                  v-model="course.description"
                  rows="4"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  :placeholder="$t('editCourse.courseDescriptionPlaceholder')"
                ></textarea>
              </div>

              <!-- Course Category -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  {{ $t('editCourse.courseCategory') }}
                </label>
                <AySelect v-model="course.category_id">
                  <option value="">{{ $t('editCourse.selectCategory') }}</option>
                  <option v-for="category in categories" :key="category.id" :value="category.id">
                    {{ category.title }}
                  </option>
                </AySelect>
              </div>

              <!-- Course Content Upload -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  {{ $t('editCourse.courseContent') }}
                </label>
                
                <!-- Upload Files Section -->
                <div class="space-y-4">
                  <!-- File Upload -->
                  <div class="border-2 border-dashed border-gray-300 rounded-lg p-6">
                    <div class="text-center">
                      <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                      </svg>
                      <div class="mt-4">
                        <label class="cursor-pointer">
                          <span class="mt-2 block text-sm font-medium text-gray-900">
                            {{ $t('editCourse.uploadFiles') }}
                          </span>
                          <input
                            ref="contentFileInput"
                            type="file"
                            multiple
                            class="sr-only"
                            accept=".pdf,.doc,.docx,.ppt,.pptx,.txt,.jpg,.jpeg,.png,.gif,.webp"
                            @change="handleContentFileChange"
                          />
                        </label>
                        <p class="mt-1 text-sm text-gray-500">
                          {{ $t('editCourse.supportedFileTypes') }}
                        </p>
                      </div>
                    </div>
                  </div>

                  <!-- Video Upload -->
                  <div class="border-2 border-dashed border-gray-300 rounded-lg p-6">
                    <div class="text-center">
                      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                      </svg>
                      <div class="mt-4">
                        <label class="cursor-pointer">
                          <span class="mt-2 block text-sm font-medium text-gray-900">
                            {{ $t('editCourse.uploadVideos') }}
                          </span>
                          <input
                            ref="videoFileInput"
                            type="file"
                            multiple
                            class="sr-only"
                            accept=".mp4,.avi,.mov,.wmv,.flv,.webm"
                            @change="handleVideoFileChange"
                          />
                        </label>
                        <p class="mt-1 text-sm text-gray-500">
                          {{ $t('editCourse.supportedVideoTypes') }}
                        </p>
                      </div>
                    </div>
                  </div>

                  <!-- Uploaded Files Display -->
                  <div v-if="uploadedFiles.length > 0 || uploadedVideos.length > 0" class="space-y-4">
                    <!-- Files List -->
                    <div v-if="uploadedFiles.length > 0">
                      <h4 class="text-sm font-medium text-gray-700 mb-2">{{ $t('editCourse.uploadedFiles') }}</h4>
                      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div
                          v-for="(file, index) in uploadedFiles"
                          :key="index"
                          class="bg-white border rounded-lg overflow-hidden shadow-sm"
                        >
                          <!-- File Preview -->
                          <div class="h-32 bg-gray-100 flex items-center justify-center relative">
                            <!-- Image Preview -->
                            <img
                              v-if="isImageFile(file)"
                              :src="getFilePreview(file)"
                              :alt="file.name"
                              class="h-full w-full object-cover"
                              @error="handleImageError"
                            />
                            <!-- PDF Preview -->
                            <div v-else-if="isPdfFile(file)" class="text-center p-4">
                              <svg class="h-12 w-12 text-red-500 mx-auto mb-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                              </svg>
                              <p class="text-xs text-gray-600">PDF</p>
                            </div>
                            <!-- Document Preview -->
                            <div v-else class="text-center p-4">
                              <svg class="h-12 w-12 text-blue-500 mx-auto mb-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                              </svg>
                              <p class="text-xs text-gray-600">{{ getFileExtension(file.name).toUpperCase() }}</p>
                            </div>
                            <!-- Remove Button -->
                            <button
                              @click="removeFile(index, 'file')"
                              class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600"
                            >
                              <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                              </svg>
                            </button>
                          </div>
                          <!-- File Info -->
                          <div class="p-3">
                            <p class="text-sm font-medium text-gray-900 truncate" :title="file.name">{{ file.name }}</p>
                            <p class="text-xs text-gray-500">{{ formatFileSize(file.size) }}</p>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Videos List -->
                    <div v-if="uploadedVideos.length > 0">
                      <h4 class="text-sm font-medium text-gray-700 mb-2">{{ $t('editCourse.uploadedVideos') }}</h4>
                      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div
                          v-for="(video, index) in uploadedVideos"
                          :key="index"
                          class="bg-white border rounded-lg overflow-hidden shadow-sm"
                        >
                          <!-- Video Preview -->
                          <div class="h-32 bg-gray-100 flex items-center justify-center relative">
                            <video
                              :src="getFilePreview(video)"
                              class="h-full w-full object-cover"
                              muted
                              preload="metadata"
                              @error="handleVideoError"
                            >
                            </video>
                            <!-- Play Icon Overlay -->
                            <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-30">
                              <svg class="h-12 w-12 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"></path>
                              </svg>
                            </div>
                            <!-- Remove Button -->
                            <button
                              @click="removeFile(index, 'video')"
                              class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600"
                            >
                              <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                              </svg>
                            </button>
                          </div>
                          <!-- Video Info -->
                          <div class="p-3">
                            <p class="text-sm font-medium text-gray-900 truncate" :title="video.name">{{ video.name }}</p>
                            <p class="text-xs text-gray-500">{{ formatFileSize(video.size) }}</p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Existing Course Content Preview -->
                  <div v-if="course.activities && course.activities.length > 0">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">{{ $t('editCourse.existingContent') }}</h4>
                    <div class="bg-gray-50 p-4 rounded-lg">
                      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        <div
                          v-for="activity in course.activities.slice(0, 6)"
                          :key="activity.id"
                          class="bg-white p-3 rounded border"
                        >
                          <div class="flex items-center gap-2 mb-2">
                            <span class="text-xs px-2 py-1 bg-blue-100 text-blue-600 rounded capitalize">
                              {{ activity.type }}
                            </span>
                            <span class="text-sm font-medium truncate">{{ activity.title }}</span>
                          </div>
                          <div class="h-20 bg-gray-100 rounded flex items-center justify-center">
                            <img
                              v-if="activity.cover"
                              :src="getAssetUrl(activity.cover)"
                              :alt="activity.title"
                              class="h-full w-full object-cover rounded"
                            />
                            <span v-else class="text-xs text-gray-400">{{ activity.type }}</span>
                          </div>
                        </div>
                      </div>
                      <p v-if="course.activities.length > 6" class="text-xs text-gray-500 mt-2">
                        {{ $t('editCourse.showingItems', { shown: 6, total: course.activities.length }) }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Course Cover -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  {{ $t('editCourse.courseCover') }}
                </label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                  <div class="space-y-1 text-center">
                    <div v-if="previewImage || course.cover" class="mb-4">
                      <img
                        :src="getCoverUrl()"
                        alt="Course cover"
                        class="mx-auto h-32 w-48 object-cover rounded-md"
                      />
                      <button
                        type="button"
                        @click="removeCover"
                        class="mt-2 text-sm text-red-600 hover:text-red-800"
                      >
                        {{ $t('editCourse.removeCover') }}
                      </button>
                    </div>
                    <svg v-else class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                      <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <div class="flex text-sm text-gray-600">
                      <label class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none">
                        <span>{{ $t('editCourse.uploadFile') }}</span>
                        <input ref="fileInput" type="file" class="sr-only" accept="image/*" @change="handleFileChange" />
                      </label>
                      <p class="pl-1">{{ $t('editCourse.orDragDrop') }}</p>
                    </div>
                    <p class="text-xs text-gray-500">{{ $t('editCourse.supportedFormats') }}</p>
                  </div>
                </div>
              </div>

              <!-- Submit Buttons -->
              <div class="flex justify-end space-x-3">
                <button
                  type="button"
                  @click="$router.back()"
                  class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
                >
                  {{ $t('editCourse.cancel') }}
                </button>
                <AyButton type="dash-blue" :disabled="loading" @click="handleSubmit">
                  {{ loading ? $t('editCourse.saving') : $t('editCourse.save') }}
                </AyButton>
              </div>
            </form>
          </div>
        </div>
  </InstructorLayout>
</template>

<script>
import { apiCall } from "@/services/apiService"
import { getRole } from '@/utils/role'
import InstructorLayout from '@/components/layout/InstructorLayout.vue'
import AyButton from '@/components/atoms/button/AyButton.vue'
import AyInput from '@/components/atoms/form/AyInput.vue'
import AySelect from '@/components/atoms/form/AySelect.vue'
import { useI18n } from 'vue-i18n'
import { useConfirm } from '@/composables/useConfirm'

const { confirm: confirmDialog, alert: alertDialog } = useConfirm()

export default {
  name: 'EditCourse',
  components: {
    InstructorLayout,
    AyButton,
    AyInput,
    AySelect,
  },
  setup() {
    const { t } = useI18n()
    return { t }
  },
  data() {
    return {
      loading: false,
      course: {
        id: null,
        title: '',
        description: '',
        cover: null,
        category_id: null,
      },
      previewImage: null,
      coverFile: null,
      categories: [],
      uploadedFiles: [],
      uploadedVideos: [],
    }
  },
  computed: {},
  async mounted() {
    await this.fetchCategories()
    await this.fetchCourse()
  },
  beforeUnmount() {
    // Clean up object URLs to prevent memory leaks
    this.uploadedFiles.forEach(file => {
      URL.revokeObjectURL(this.getFilePreview(file))
    })
    this.uploadedVideos.forEach(video => {
      URL.revokeObjectURL(this.getFilePreview(video))
    })
  },
  methods: {
    getAssetUrl(path) {
      if (!path) return '/imgHolders/CardHolderImage.jpg'
      if (path.startsWith('http')) return path
      if (path.startsWith('/')) return path
      return `/uploads/${path.replace(/\\/g, '/')}`
    },
    async fetchCategories() {
      try {
        const response = await apiCall('GET', '/api/home')
        this.categories = response.categories || []
      } catch (error) {
        console.error('Failed to fetch categories:', error)
      }
    },
    async fetchCourse() {
      try {
        const courseId = this.$route.params.id
        const response = await apiCall('GET', `/api/instructor/course/${courseId}`)
        // Ownership guard: only the course owner may edit it.
        if (response.is_owner === false) {
          this.$toast.error(this.$t('createCourseModal.notOwner'))
          this.$router.push(`/${this.$role}/exam/courses`)
          return
        }
        this.course = response.course
        console.log('Loaded course data:', this.course)
        console.log('Course cover:', this.course.cover)
      } catch (error) {
        console.error('Failed to fetch course:', error)
        this.$router.push(`/${localStorage.getItem('role') || 'teacher'}/exam/courses`)
      }
    },
    handleFileChange(event) {
      const file = event.target.files[0]
      if (!file || !file.type.startsWith('image/')) return
      
      this.coverFile = file
      const reader = new FileReader()
      reader.onload = (e) => {
        this.previewImage = e.target.result
      }
      reader.readAsDataURL(file)
    },
    removeCover() {
      this.previewImage = null
      this.coverFile = null
      this.course.cover = null
      if (this.$refs.fileInput) {
        this.$refs.fileInput.value = ''
      }
    },
    getCoverUrl() {
      if (this.previewImage) {
        return this.previewImage
      }
      if (this.course.cover) {
        // Handle both full URLs and relative paths
        if (this.course.cover.startsWith('http') || this.course.cover.startsWith('/uploads/')) {
          return this.course.cover
        }
        return `/uploads/${this.course.cover}`
      }
      return null
    },
    async handleContentFileChange(event) {
      const files = Array.from(event.target.files)
      console.log('Files selected:', files)
      for (const file of files) {
        console.log('Processing file:', file.name, file.type)
        if (this.isValidFileType(file)) {
          this.uploadedFiles.push(file)
          console.log('File added to uploadedFiles:', file.name)
        } else {
          console.log('Invalid file type:', file.type)
          await alertDialog({ message: `Invalid file type: ${file.type}. Please select a supported file type.` })
        }
      }
      console.log('Total uploaded files:', this.uploadedFiles.length)
      // Clear the input
      if (this.$refs.contentFileInput) {
        this.$refs.contentFileInput.value = ''
      }
    },
    async handleVideoFileChange(event) {
      const files = Array.from(event.target.files)
      console.log('Videos selected:', files)
      for (const file of files) {
        console.log('Processing video:', file.name, file.type)
        if (this.isValidVideoType(file)) {
          this.uploadedVideos.push(file)
          console.log('Video added to uploadedVideos:', file.name)
        } else {
          console.log('Invalid video type:', file.type)
          await alertDialog({ message: `Invalid video type: ${file.type}. Please select a supported video type.` })
        }
      }
      console.log('Total uploaded videos:', this.uploadedVideos.length)
      // Clear the input
      if (this.$refs.videoFileInput) {
        this.$refs.videoFileInput.value = ''
      }
    },
    isValidFileType(file) {
      const allowedTypes = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'text/plain',
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/gif',
        'image/webp'
      ]
      return allowedTypes.includes(file.type)
    },
    isValidVideoType(file) {
      const allowedTypes = [
        'video/mp4',
        'video/avi',
        'video/quicktime',
        'video/x-ms-wmv',
        'video/x-flv',
        'video/webm'
      ]
      return allowedTypes.includes(file.type)
    },
    removeFile(index, type) {
      if (type === 'file') {
        const file = this.uploadedFiles[index]
        if (file) {
          // Revoke the object URL to free memory
          const objectUrl = URL.createObjectURL(file)
          URL.revokeObjectURL(objectUrl)
        }
        this.uploadedFiles.splice(index, 1)
      } else if (type === 'video') {
        const video = this.uploadedVideos[index]
        if (video) {
          // Revoke the object URL to free memory
          const objectUrl = URL.createObjectURL(video)
          URL.revokeObjectURL(objectUrl)
        }
        this.uploadedVideos.splice(index, 1)
      }
    },
    formatFileSize(bytes) {
      if (bytes === 0) return '0 Bytes'
      const k = 1024
      const sizes = ['Bytes', 'KB', 'MB', 'GB']
      const i = Math.floor(Math.log(bytes) / Math.log(k))
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
    },
    isImageFile(file) {
      return file.type.startsWith('image/')
    },
    isPdfFile(file) {
      return file.type === 'application/pdf'
    },
    getFilePreview(file) {
      return URL.createObjectURL(file)
    },
    getFileExtension(filename) {
      return filename.split('.').pop() || ''
    },
    handleImageError(event) {
      console.error('Image load error:', event)
    },
    handleVideoError(event) {
      console.error('Video load error:', event)
    },
    async handleSubmit() {
      this.loading = true
      try {
        const formData = new FormData()
        formData.append('title', this.course.title)
        formData.append('description', this.course.description || '')
        
        if (this.course.category_id) {
          formData.append('category_id', this.course.category_id)
        }
        
        if (this.coverFile) {
          formData.append('cover', this.coverFile)
        } else if (this.course.cover === null) {
          // Explicitly remove cover if it was set to null
          formData.append('remove_cover', '1')
        }
        
        // Add method override for FormData
        formData.append('_method', 'PUT')
        
        // Add uploaded files
        this.uploadedFiles.forEach((file, index) => {
          formData.append(`content_files[${index}]`, file)
        })
        
        // Add uploaded videos
        this.uploadedVideos.forEach((video, index) => {
          formData.append(`content_videos[${index}]`, video)
        })
        
        const response = await apiCall('POST', `/api/instructor/course/${this.course.id}`, formData)
        
        // Show success message
        await alertDialog({ message: 'Course updated successfully!' })

        this.$router.push(`/${getRole()}/course/${this.course.id}`)
      } catch (error) {
        console.error('Failed to update course:', error)
        await alertDialog({ message: 'Failed to update course. Please try again.' })
      } finally {
        this.loading = false
      }
    }
  }
}
</script>

<style scoped>
/* Add any custom styles here */
</style>