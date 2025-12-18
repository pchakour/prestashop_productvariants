function onVariantSelectChange(select) {
  const url = select.value;
  if (url) {
    window.location.href = url;
  }
}