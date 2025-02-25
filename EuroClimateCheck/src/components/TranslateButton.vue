<script setup>
import { ref } from 'vue';
import Button from 'primevue/button';
import { translate } from '../utils/translate';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
  getSourceText: {
    type: Function,
    required: true
  },
  updateTargetField: {
    type: Function,
    required: true
  },
  apiConfig: {
    type: Object,
    required: true
  }
});

const loading = ref(false);
const toast = useToast();

const handleTranslate = async () => {
  loading.value = true;
  await translate(props.getSourceText(), props.updateTargetField, props.apiConfig, toast);
  loading.value = false;
};
</script>

<template>
  <Button 
    :icon="loading ? 'pi pi-spin pi-spinner' : 'pi pi-globe'"
    @click="handleTranslate">Translate</Button>
</template> 