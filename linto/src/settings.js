console.log('settings script loaded')
document.getElementById('linto-settings-form').addEventListener('submit', async (e) => {
  e.preventDefault();

  const url = OC.generateUrl('apps/linto/config')
  const apiKey = document.getElementById('linto-api-key').value;
  const data = {
    values: {
      apiKey
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
