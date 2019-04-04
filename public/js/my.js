function submitOnce(obj, form)
{
    obj.disabled = true;
    setTimeout(function () {
        form.submit();
    }, 100);
}

document.getElementById("disableButton").addEventListener("click", function (event) {
    event.preventDefault();
});