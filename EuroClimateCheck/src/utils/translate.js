export const translate = async (text, updateField, apiConfig, toast) => {
  if (!text) {
    toast.add({ severity: 'warn', summary: 'Warning', detail: 'No text to translate', life: 3000 });
    return;
  }

  const authHeaders = new Headers();
  authHeaders.append("X-API-KEY", apiConfig.apikey);
  authHeaders.append("X-DOMAIN", apiConfig.domain);
  authHeaders.append("Content-Type", "application/json");

  try {
    const response = await fetch(`${apiConfig.endpoint}/translate`, {
      method: 'POST',
      headers: authHeaders,
      body: JSON.stringify({ text })
    });

    if (!response.ok) {
      throw new Error('Translation failed');
    }

    const result = await response.text();
    updateField(result);
  } catch (error) {
    console.error('Translation error:', error);
    toast.add({ severity: 'error', summary: 'Error', detail: 'Translation failed', life: 3000 });
  }
}; 