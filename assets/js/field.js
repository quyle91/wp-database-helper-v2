document.addEventListener('click', function (e) {
    if (e.target.classList.contains('wpdh-control')) {
        console.log({
            name: e.target.name,
            value: e.target.value
        });
    }
});
