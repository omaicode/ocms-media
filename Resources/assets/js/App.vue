<template>
<div>
    <input type="hidden" :name="name" :value="selectedMediaUuid || value">
    <MediaButton :placeholder="placeholder" @click="buttonClicked" :image="selectedMediaUrl || preview"/>
    <vue-final-modal
        name="mediaModal"
        v-model="modalShow"
        classes="modal-container"
        content-class="modal-custom"
    >
        <div class="modal-custom--title">
            {{label}}
        </div>
        <div class="modal-custom--close" @click="modalShow = false">
            <i class="fas fa-times"></i>
        </div>        
        <div class="modal-custom--content">       
            <div class="d-flex justify-content-between align-items-center border-bottom border-primary">
                <!-- Nav tabs -->
                <ul class="nav nav-tabs tab-media w-100" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="media-tab" data-bs-toggle="tab" data-bs-target="#media" type="button" role="tab" aria-controls="media" aria-selected="true">{{trans.media_library}}</button>
                    </li>
                </ul>            
                <div class="input-group input-group-sm" style="width: 200px">
                    <input type="text" v-model="search" class="form-control" :placeholder="trans.search" aria-describedby="searchSuffix">
                    <span class="input-group-text" id="searchSuffix"><i class="fas fa-search"></i></span>
                </div>    
            </div>     
            <div class="row g-0" style="height: 350px">
                <div class="col-12 col-xl-9 col-lg-9">
                    <div class="tab-content">
                        <div class="tab-pane active" id="media" role="tabpanel" aria-labelledby="media-tab">                          
                            <div class="media-grid">
                                <div class="media-dropzone">
                                    <div class="dropzone" :class="{active: isDragActive}" v-bind="getRootProps()">
                                        <input v-bind="getInputProps()">
                                        {{trans.click_or_drop}}
                                    </div>
                                </div>               
                                <div class="media-list">
                                    <div class="media-item" v-for="(media, index) in queueFiles" :key="`file_${index}`">
                                        <img :src="media.url">
                                    </div>                                 
                                    <div 
                                        class="media-item" 
                                        :class="{selected: media.id == selectedMedia?.id}"
                                        v-for="(media, index) in mediaList" 
                                        :key="`media_${index}`"
                                        @click="selectMedia(media)"
                                    >
                                        <img :src="media.preview_url">
                                    </div>                                 
                                </div>                   
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-xl-3 col-lg-3">
                    <div class="attachment-details px-3 pt-2 pb-5" v-if="selectedMedia">
                        <p class="fw-bold mb-2">{{ trans.attachment_details }}</p>
                        <div class="detail-image">
                            <img :src="selectedMedia.preview_url">
                        </div>
                        <div class="detail-information">
                            <div class="mb-1"><b>File name:</b> {{ selectedMedia.file_name }}</div>
                            <div class="mb-1"><b>Type:</b> {{ selectedMedia.mime_type }}</div>
                            <div class="mb-1"><b>Size:</b> {{ selectedMedia.size / 1000 }}KB</div>
                            <div class="mb-1"><input class="form-control form-control-sm" :value="selectedMedia.original_url"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-3 d-flex w-100 justify-content-end">
                <button type="button" class="btn btn-primary" :disabled="!selectedMedia" @click="insert">
                    {{ trans.label || 'Insert' }} {{ label }}
                </button>
            </div>
        </div>
    </vue-final-modal>
</div>
</template>

<script setup>
    import _ from 'lodash'
    import axios from 'axios';
    import { ref, onMounted, reactive, watch } from 'vue';
    import { $vfm, VueFinalModal, ModalsContainer } from 'vue-final-modal';
    import { useDropzone } from "vue3-dropzone";
    import MediaButton from './Button.vue';
    
    const props = defineProps({
        name: {
            type: String,
            default: 'media'
        },
        label: {
            type: String,
            default: 'Media Gallery'
        },
        placeholder: {
            type: String
        },
        trans: {
            type: Object,
            default: () => ({
                media_library: "Media library",
                upload: "Upload",
                search: "Search...",
                click_or_drop: "Click or Drop your media here",
                attachment_details: "Attachment Details",
                insert: "Insert"
            })
        },
        url: {
            default: '/'
        },
        modelClass: String,
        modelCollection: {
            type: String,
            default: 'default'
        },
        value: String,
        preview: String
    })

    const components = {
        MediaButton
    }

    //Data
    const $axios =  axios.create({
        baseURL: props.url,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    });
    const modalShow = ref(false);
    const mediaList = ref([])
    const selectedMedia = ref(null)
    const selectedMediaUuid = ref(null)
    const selectedMediaUrl = ref(null)
    const search = ref('')
    const searchTimer = ref(null)
    const queueFiles = reactive([])
    const state = reactive({
        files: [],
    });    

    const fetchMediaList = async () => {
        try {
            const { data } = await $axios.post('list', {
                model: props.modelClass,
                collection: props.modelCollection,
                search: search.value
            })

            if(data.success) {
                mediaList.value = data.data
            }
        } catch (err) {
            console.log(err)
        }
    }     

    //Watch
    watch(queueFiles, async (newval) => {
        for(let idx in newval) {
            try {
                const media = queueFiles[idx]
                if(media.uploading) continue

                const formData = new FormData
                formData.append('file', media.file)
                formData.append('model', props.modelClass)
                formData.append('collection', props.modelCollection)

                queueFiles[idx].uploading = true
                const { data } = await $axios.post('upload', formData)

                if(data.success) {
                    _(queueFiles).splice(idx).value()
                    mediaList.value = [data.data].concat(mediaList.value)
                } else {
                    queueFiles[idx].uploadError = true
                    queueFiles[idx].error_msg = data.message
                }
            } catch (err) {
                console.log(err)
            }
        }
    }, {
        deep: true
    })
    watch(search, function() {
        clearTimeout(searchTimer.value)
        searchTimer.value = setTimeout(async () => {
            await fetchMediaList()
        }, 500)
    })
    //Functions
    const insert = () => {
        if(!selectedMedia.value) {
            return
        }

        selectedMediaUuid.value = selectedMedia.value.uuid
        selectedMediaUrl.value = selectedMedia.value.preview_url
        modalShow.value = false
    }

    const buttonClicked = () => {
        modalShow.value = true
    };

    const onDrop = (acceptFiles, rejectReasons) => {
        state.files = acceptFiles;
        state.files.forEach(file => {
            queueFiles.push({
                uploading: false,
                uploaded: false,
                uploadError: false,
                error_msg: '',
                url: 'data:image/gif;base64,R0lGODlh8wC9APIAAPj4+Nzc3MLCwqCgoHp6ekxMTBwcHP///yH/C05FVFNDQVBFMi4wAwEAAAAh/wtYTVAgRGF0YVhNUDw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiAoV2luZG93cykiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6QjYyNjczMDkxMzgwMTFFMzg4MzU5RjhCNkRBRDgzOUQiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6QjYyNjczMEExMzgwMTFFMzg4MzU5RjhCNkRBRDgzOUQiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDpCNjI2NzMwNzEzODAxMUUzODgzNTlGOEI2REFEODM5RCIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDpCNjI2NzMwODEzODAxMUUzODgzNTlGOEI2REFEODM5RCIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PgH//v38+/r5+Pf29fTz8vHw7+7t7Ovq6ejn5uXk4+Lh4N/e3dzb2tnY19bV1NPS0dDP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',
                file
            })
        })
    }

    const { 
        getRootProps, 
        getInputProps, 
        isDragActive, 
        ...rest 
    } = useDropzone({ 
        onDrop,
        accept: ['image/*', 'video/*'],
        multiple: true
    });    

    const selectMedia = (media) => {
        selectedMedia.value = media
    }
    
    onMounted(async () => {             
        await fetchMediaList()
    })
</script>