function confirmDelete(id) {
    let result = confirm("Are you sure you want to delete this log?");
    if (result) {
        window.location.href = "delete_log.php?id=" + id;
    }
}

document.addEventListener("DOMContentLoaded", function() {
    const form = document.querySelector("form");
    if (form) {
        form.addEventListener("submit", function(event) {
            let sleepInput = document.querySelector("input[name='sleep']");
            let waterInput = document.querySelector("input[name='water']");

            if (sleepInput && sleepInput.value > 24) {
                alert("You cannot sleep more than 24 hours in a day!");
                event.preventDefault();
            }
            if (waterInput && waterInput.value > 10) {
                alert("That is too much water! Please check your input.");
                event.preventDefault();
            }
        });
    }
});