const adl_legal_pages__modal = (show = true) => {
    const modal = document.getElementById("adl-legal-modal");
    if (show) {
        modal.style.display = "";
    } else {
        modal.style.display = "none";
    }
};
