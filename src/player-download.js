import JSZip from 'jszip';
// import { saveAs } from 'file-saver';
import streamSaver from 'streamsaver';

// Ajouter un bouton pour télécharger les favoris
export function setupDownloadButton(player, atts = {}) {
    
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
                    // Récupérer les données du fichier en tant qu'ArrayBuffer
                    return response.arrayBuffer();
                })
                .then(function(buffer) {
                    downloadButton.textContent = 'Fetching ' + filesAdded + '/' + currentPlaylist.length;
                    // Extract the filename from the src attribute
                    var filename = item.sources.src.split('/').pop();

                    // Ajouter le buffer au zip avec the original filename
                    zip.file(filename, buffer, {binary:true});

                    // Incrémenter le compteur de fichiers ajoutés
                    filesAdded++;

                    buffer = null; // Supprimer la référence à buffer
                });

                // Ajouter la promesse à notre tableau de promesses
                fetchPromises.push(fetchPromise);
            });

            // Créer un TransformStream pour suivre la progression
            const ts = new TransformStream({
                transform(chunk, controller) {
                    // Mettre à jour la progression
                    updateProgress(chunk.length);
                    // Passer le chunk au flux inscriptible
                    controller.enqueue(chunk);
                }
            });

            let contentLength;

            // Attendre que toutes les promesses soient résolues
            Promise.all(fetchPromises)
            .then(function() {
                // Générer le fichier zip de manière asynchrone
                downloadButton.textContent = 'Packing';
                return zip.generateAsync({type:"uint8array", streamFiles:true}, function(metadata) {
                    // Mettre à jour la progression
                    downloadButton.textContent = `Packing: ${metadata.percent.toFixed(2)}%`;
                });
            })
            .then(function(content) {
                downloadButton.textContent = 'Saving';
                // Utiliser le titre de la page comme nom du fichier
                var zipname = document.title ? document.title : window.location.pathname.split('/').pop();
                contentLength = content.length; // Mettre à jour contentLength

                // Créer un nouveau fichier avec streamSaver
                streamSaver.mitm = '/.cache/dist/mitm.html';
                const fileStream = streamSaver.createWriteStream(zipname + ".zip", {
                    size: content.length,
                    writableStrategy: undefined,
                    readableStrategy: undefined
                });

                // Créer un flux lisible à partir du contenu
                const readable = new ReadableStream({
                    start(controller) {
                        controller.enqueue(content);
                        controller.close();
                    }
                });

                // Pipe le flux lisible au TransformStream, puis au flux inscriptible
                return readable.pipeThrough(ts).pipeTo(fileStream);
            })
            .then(function() {
                // Calculer le délai en fonction de la taille du contenu
                var delay = contentLength / (15 * 1024 * 1024) * 1000; //   15Mo/s
                downloadButton.textContent = 'Writing on disk...';
                
                // Utiliser setTimeout pour ajouter un délai avant de revenir à initialButtonContent
                setTimeout(function() {
                downloadButton.textContent = initialButtonContent;
                downloadButton.disabled = false; // Réactiver le bouton
                }, delay);
            });

            // Fonction pour mettre à jour la progression
            function updateProgress(chunkLength) {
                // Mettre à jour la barre de progression ou un autre indicateur visuel ici
                downloadButton.textContent = `Processed ${chunkLength} bytes`;
            }
        });
    });
}
    