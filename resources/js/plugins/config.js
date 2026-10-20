import config from '../utils/config.js'

export default {
    install(app) {
        app.config.globalProperties.$config = config
        app.provide('config', config)
    }
}