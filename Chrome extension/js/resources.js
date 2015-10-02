/* 
 * HTML templates for download progress div and injected table rows 
 * in Compose view. Ideally these would be in separate HTML files and 
 * loaded at runtime, but Chrome does not allow XMLHttpRequests
 * from local files. 
 *
 * Similarly, the javascript code for the worker thread (see Model.js)
 * could not be loaded as a file at runtime, so I had to resort to storing
 * it here as a string. 
 */
var Templates = function() {
    return {updatedViewDownload:
'<div>' + 
    '<div class="cloudy_updatedview_download" tabindex="2">' +
        '<div class="cloudy_updatedview_download_wrapper">' + 
            '<span class="cloudy_updatedview_download_msg">' + 
                'Downloading:' + 
            '</span>' + 
            '<span> </span>' +
            '<span class="cloudy_updatedview_download_filename">Folder.jpg' + 
            '</span>' + 
        '</div>' + 
        '<div class="cloudy_updatedview_download_size">(40K)</div>' +
        '<img class="cloudy_updatedview_download_statusicon" tabindex="1" />' +
    '</div>' +
'</div>',
    }
            
}
