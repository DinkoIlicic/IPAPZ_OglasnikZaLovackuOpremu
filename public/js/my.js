function submitOnce(obj, form)
{
    obj.disabled = true;
    setTimeout(function () {
        form.submit();
    }, 100);
}