<template>
    <div class="media-wrapper">
        <button 
            type="button" 
            class="media-button" 
            @click="$emit('click')"
            :style="`${image ? `background-image: url(${image})` : ''}`"
        >
            {{!image ? placeholder : ''}}
        </button>
    </div>
</template>

<script setup>
    const props = defineProps({
        placeholder: {
            type: String,
            default: 'Select Image'
        },

        image: {
            type: String,
            default: ''
        }
    })
    const emit = defineEmits(['click'])
</script>

<style lang="scss" scoped>
.media-wrapper {
    position: relative;

    .media-button {
        display: block;
        text-decoration: none;
        font-family: inherit;
        font-weight: 400;
        font-size: 13px;
        margin: 0;
        border: 0;
        cursor: pointer;
        -webkit-appearance: none;
        transition: box-shadow .1s linear;
        align-items: center;
        box-sizing: border-box;
        border-radius: 8px;
        color: #1e1e1e;   
        transition: all .1s ease-out;
        box-shadow: 0 0 0 0 var(--bs-primary); 
        width: 100%;           
        background-color: #f0f0f0;
        min-height: 150px;
        line-height: 20px;
        padding: 8px 0;
        text-align: center;   
        border: 1px solid #d5d5d5;
        background-repeat: no-repeat;
        background-size: contain;       
        background-position: center;         
        
        &:hover {
            background-color: #d5d5d5;
        }

        &:active {
            border: 2px solid var(--bs-primary)
        }
    }
}
</style>