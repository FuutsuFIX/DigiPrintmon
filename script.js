var requestOptions = {
    method: 'GET',
    redirect: 'follow'
};
var database = []

fetch("https://digimoncard.io/api-public/getAllCards.php?sort=name&series=Digimon Card Game&sortdirection=asc", requestOptions)
    .then(response => response.text())
    .then(result => console.log(result))
    .catch(error => console.log('error', error));

console.log(database)