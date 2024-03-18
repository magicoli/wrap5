import JSZip from 'jszip';
import { saveAs } from 'file-saver';

// Ajouter un bouton pour télécharger les favoris
export function setupDownloadButton(player, atts = {}) {
    // Check if JSZip and file-saver libraries are available
    if (!JSZip || !saveAs) {
        return;
    }
    
    player.on('ready', function() {

        var downloadButton = document.createElement('button');
        
        downloadButton.innerHTML = 'Download folder';
        downloadButton.className = 'download-folder-button';
        
        // Créer un nouvel élément li et lui attribuer la classe action
        var li = document.createElement('li');
        li.className = 'action';
        
        // Ajouter le bouton à l'élément li
        li.appendChild(downloadButton);
        
        // Ajouter l'élément li à l'élément actions
        document.getElementById('actions').appendChild(li);
        
        // Gestionnaire d'événements pour le bouton de téléchargement
        downloadButton.addEventListener('click', function() {
            var initialButtonContent = downloadButton.textContent;
            
            downloadButton.disabled = true;
            
            // Obtenir la liste de lecture actuelle
            var currentPlaylist = player.playlist();
            
            // Si la liste de lecture est vide, afficher un message d'erreur et arrêter l'exécution
            if (currentPlaylist.length === 0) {
                downloadButton.disabled = false; // Réactiver le bouton
                return;
            }
            downloadButton.textContent = 'Preparing , please wait...';
            
            // Créer une nouvelle instance JSZip
            var zip = new JSZip();
            
            // Créer un tableau pour stocker les promesses de récupération des fichiers
            var fetchPromises = [];
            
            // Créer un compteur pour suivre le nombre de fichiers ajoutés
            var filesAdded = 1;
            
            // Ajouter chaque fichier de la liste de lecture au zip
            currentPlaylist.forEach(function(item, index) {
                // Utiliser l'API Fetch pour récupérer les données du fichier
                var fetchPromise = fetch(item.sources.src)
                .then(function(response) {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.blob();
                })
                .then(function(blob) {
                    downloadButton.textContent = 'Packing ' + filesAdded + '/' + currentPlaylist.length;
                    // Extract the filename from the src attribute
                    var filename = item.sources.src.split('/').pop();

                    // Ajouter le blob au zip avec the original filename
                    zip.file(filename, blob, {binary:true});

                    // Incrémenter le compteur de fichiers ajoutés
                    filesAdded++;

                    // Mettre à jour le texte du bouton avec la progression
                });

                // Ajouter la promesse à notre tableau de promesses
                fetchPromises.push(fetchPromise);
            });

            // Attendre que toutes les promesses soient résolues
            Promise.all(fetchPromises)
            .then(function() {
                // Générer le fichier zip de manière asynchrone
                downloadButton.textContent = 'Saving';
                return zip.generateAsync({type:"blob"});
            })
            .then(function(content) {
                // Utiliser le titre de la page comme nom du fichier
                var zipname = document.title ? document.title : window.location.pathname.split('/').pop();

                // Utiliser FileSaver.js pour sauvegarder le fichier avec le nom du titre de la page
                saveAs(content, zipname + ".zip");
            })
            .then(function() {
                downloadButton.textContent = initialButtonContent;
                downloadButton.disabled = false; // Réactiver le bouton
            });
        });
    });
}
