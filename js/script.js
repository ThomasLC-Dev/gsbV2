//Show/Hide popup
function openPopup(){
    document.querySelector('.popup').classList.add("open-popup")
    document.querySelector('.page').classList.add("blur")

    return false;
}

function closePopup(){
    document.querySelector('.popup').classList.remove("open-popup")
    document.querySelector('.page').classList.remove("blur")
}