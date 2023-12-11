const fs = require('fs');
const path = require('path');

const folderPath = './img';

fs.readdir(folderPath, (err, files) => {
    if (err) {
        console.error('Errore nella lettura della cartella:', err);
        return;
    }

    files.forEach((file) => {
        const oldPath = path.join(folderPath, file);
        const fileExtension = path.extname(file);
        const fileName = path.basename(file, fileExtension);
        const newExtension = fileExtension.toLowerCase();
        const newName = fileName.toUpperCase() + newExtension;

        if (file === newName) {
            console.log(`Il file ${file} è già case sensitive.`);
            return;
        }

        const newPath = path.join(folderPath, newName);

        fs.rename(oldPath, newPath, (renameErr) => {
            if (renameErr) {
                console.error(`Errore nel rinominare il file ${file}:`, renameErr);
            } else {
                console.log(`Il file ${file} è stato rinominato in ${newName}`);
            }
        });
    });
});
