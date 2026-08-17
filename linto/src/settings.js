console.log('settings script loaded')
document.getElementById('linto-settings-form').addEventListener('submit', async (e) => {
  e.preventDefault();

  const url = OC.generateUrl('apps/linto/config')
  const apiKey = document.getElementById('linto-api-key').value;
  const apiUrl = document.getElementById('linto-api-url').value;
  const organisationId = document.getElementById('linto-organisation-id').value;
  const data = {
    values: {
      apiKey,
      apiUrl,
      organisationId
    }
  }
  const response = await fetch(url,
    {
      method: 'POST',
      body: JSON.stringify(data),
      headers: {
        requesttoken: OC.requestToken,
        'Content-Type': 'application/json',
      }
    }
  )
  return false
});
