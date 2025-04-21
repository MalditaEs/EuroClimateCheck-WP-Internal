<template>
  <div class="image-uploader">
    <div class="image-preview ec:mb-2 ec:border ec:border-dashed ec:border-slate-300 ec:p-2 ec:min-h-24 ec:flex ec:items-center ec:justify-center" @click="openMediaLibrary">
      <img v-if="imageUrl" :src="imageUrl" alt="Selected image" class="ec:max-w-full ec:max-h-48 ec:block" />
      <span v-else class="ec:text-slate-500">Click to select an image</span>
    </div>
    <Button @click="openMediaLibrary" severity="secondary" outlined class="ec:mr-2">
      <i class="fa-solid fa-image ec:mr-2"></i> {{ imageUrl ? 'Change Image' : 'Select Image' }}
    </Button>
    <Button v-if="imageUrl" @click="removeImage" severity="danger" outlined>
      <i class="fa-solid fa-trash ec:mr-2"></i> Remove Image
    </Button>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import Button from 'primevue/button';

const props = defineProps({
  modelValue: String // Accepts the image URL
});

const emit = defineEmits(['update:modelValue']);

const imageUrl = ref(props.modelValue || '');

watch(() => props.modelValue, (newValue) => {
  imageUrl.value = newValue || '';
});

const openMediaLibrary = () => {
  if (typeof wp === 'undefined' || !wp.media) {
    console.error('WordPress media library not available.');
    alert('WordPress media library not available.');
    return;
  }

  const mediaFrame = wp.media({
    title: 'Select or Upload Image',
    button: {
      text: 'Use this image'
    },
    multiple: false // Only allow single image selection
  });

  mediaFrame.on('select', () => {
    const attachment = mediaFrame.state().get('selection').first().toJSON();
    imageUrl.value = attachment.url;
    emit('update:modelValue', attachment.url);
  });

  mediaFrame.open();
};

const removeImage = () => {
  imageUrl.value = '';
  emit('update:modelValue', '');
};

</script>

<style scoped>
.image-preview {
  cursor: pointer;
}
</style> 