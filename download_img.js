const https = require('https');
const fs = require('fs');
const path = require('path');

// Variabile per il controllo delle carte uguali
const checkDuplicates = true; // Imposta a 'true' per abilitare il controllo delle carte uguali


// Funzione per scaricare un'immagine da un URL e salvarla come file
function downloadImage(url, filename) {
    return new Promise((resolve, reject) => {
        const file = fs.createWriteStream(filename);
        https.get(url, (response) => {
            response.pipe(file);
            file.on('finish', () => {
                file.close(resolve);
            });
        }).on('error', (error) => {
            fs.unlink(filename, () => {
                reject(error);
            });
        });
    });
}

// Funzione principale per scaricare le immagini dal database
async function downloadImages(database) {
    const base_url = 'https://images.digimoncard.io/images/cards/';
    const imgFolder = path.join(__dirname, 'img');

    // Crea la cartella "img" se non esiste già
    if (!fs.existsSync(imgFolder)) {
        fs.mkdirSync(imgFolder);
    }

    // Itera attraverso ogni elemento del database
    for (const item of database) {
        const cardnumber = item.cardnumber;
        if (cardnumber) {
            // Rimuovi gli spazi dal numero della carta
            const sanitizedCardnumber = cardnumber.replace(/\s/g, '');
            const image_name = sanitizedCardnumber + '.jpg';
            const image_path = path.join(imgFolder, image_name);

            // Verifica se il file esiste già nella cartella "img" solo se il controllo delle carte uguali è abilitato
            if (checkDuplicates && fs.existsSync(image_path)) {
                console.log(`Skipped existing image: ${image_name}`);
                continue; // Passa all'immagine successiva
            }

            // Costruisci l'URL dell'immagine
            const image_url = base_url + sanitizedCardnumber + '.jpg';

            try {
                // Scarica e salva l'immagine
                await downloadImage(image_url, image_path);
                console.log(`Downloaded image: ${image_name}`);
            } catch (error) {
                console.log(`Failed to download image: ${image_name}`);
            }
        }
    }
}

// Funzione per ottenere il database delle carte e avviare il processo di download
async function fetchCardDatabase() {
    const requestOptions = {
        method: 'GET',
        redirect: 'follow'
    };

    try {
        // Effettua la richiesta per ottenere il nuovo database delle carte
        const response = await fetch("https://digimoncard.io/api-public/getAllCards.php?sort=name&series=Digimon Card Game&sortdirection=asc", requestOptions);
        const database = await response.json();

        // Scarica le nuove immagini dal database
        await downloadImages(database);
    } catch (error) {
        console.log("Failed to fetch card database:", error);
    }
}

// Avvia il processo di fetch e download delle immagini
fetchCardDatabase();
