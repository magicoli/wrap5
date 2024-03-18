## Changelog

Unreleased changes
* new download button
* save minisite root path
* (dev) added npm file-saver and jzip packages

5.0.0-dev-2
* preserve child folders order
* nav max width

5.0.0-dev
* new 5.x branch, let's redo all this again, but better
* rewrote basic navigation and player funtionalties
* new favorite button and filter
* thumbnails
  - fallback to fontawesome icon instead of images
  - auto generate video thumbnails if not present
* minimum php version to 7.4
* read each folder parameters in .wrap.json
  - new makejson script (legacy playlist converter)
* cleancasting new time argument (delete files with duration longer than -t)
* castingsplit fix IFS bug
* composer libraries
  - mimey (to get mime types)
  - video.js (the player)
  - ffmpeg (for thumbnails and future video manipulations)
  - bump-version (dev, to bump versions)
  - webpack (dev)

3.1.1
* new script checkmp4
* new script cleancasting
* new script castingsplit
* new script castingselection
* new .htaccess
* new TOC (table of content)
* added php-ffmpeg/php-ffmpeg package
* added mkv extension to mediacopy
* disabled MacOS Safari notice
* don't exit if notify-send is not installed, only display message
* reference only: useful resource to find crisper image for thumbnails, worth a try

* casting-server, casting-client: don't try to use "open" command
* casting-client
  - use mediawatch-remote
* casting-server
  - added VS Code as editor
* castingchecktimes 
  - avoid error caused by temp qt-thumbnails files
* casting-helpers 
  - don't replace director by client
  - read prefs from base, director and job directory
  - store client/server status to castingmode variable
* castingrolesbycomment
  - fix sort order for more than 9 castings
  - make TOC if toc folder is present
  - allow several roles
* makemp4
  - error with multi lines ffprobe result
  - aliases issues
  - blur
* makeplaylist
  - correctly handle comedians in multiple sections
* mediawatch
  - added speech notification
  - fix launch without setting director
  - fix merge errors
  - fix nothing found if director not set
  - also detect new completed files in upload, not only temp php files
  - detect if upload is for current director, correctly quit if match
  - exit with error if notify-send is not installed
* rethumb-comedian
  - rethumb-comedian added tests, only process if comedian and video found
  - ignore comments when finding matching video
  - fix error when time is between 0 and 1
  - use casting-helpers
  - allow floating number for time
  - turn off on screen error reporting
  - fix undefined constant notice

3.1.0
* front-end:
  - updated playable formats
  - new flex/grid-based default theme
  - smooth scroll to clicked thumb
  - stay on clicked thumb position after playing video
  - don't hide playlist in background
  - use versioning to launch css and js, to avoid cache issues
* fix file not found when filename contains spaces or special characters
* new mediadeduplicate script
* batchff: quote file names
* medialoop: show countdown between instances
* casting-server: include folder in when launching atom
* batchloop: show still unprocessed videos
* makemp4 added allblur preference, whatever it is
* castingchecktime: ignore <5 sec as default, read .casting conf in client/job or casting for custom thresold and other custom vars
* casting-client launch mediawatch in 4th window
* casting-client/server prefer atom editor if present
* batchloop show missing videos (still in queue)
* added icons
* fix #1 don't try to play audio files (download only)
* removed -threads auto from ffmpeg args
* added vsync to moviemerge

3.0.3
* removed useless files from release package
* cleaner changelog and minor cosmetic changes
* fix hardcoded path
* new back-end scripts

3.0.0
* This is a major upgrade. However, there is no specific upgrade path for the web content, it is backward compatible with 2.x as the actual major change is the inclusion of new back-end scripts.
* PHP7-ready
* Optimize bandwidth, load video only on request
* Optimize video display
* New back-end tools
  (old ones actually, merged from another repo, will be maintained here now)

2.4.8
* new: bootstrap layout, becomes default
* fix: protect wrap own directory
* added: support for Canon .MXF files
* added: list printing
* updated: php7-ready (well that's the most important)
* updated: optimize bandwidth, load video only on request
* udpated: clean html5 video code
* enhanced: Cleaner display
* added reference code to write name on large thumbnail (not active)
* deprecated: old libraries (motools, jQuery-File-Upload, jd.gallery)
* deprecated: former browser.* naming (still compatible though)
* fix: only try to detect mobile if Mobile_Detect class is present

1.11.0
* new: SSL support
* new: theming (work in progress)
* new: handle remote pages
* new: html5 video subtitles support
* added theora ogg & ogv support
* added modules videosub, video-js and modernizr
* added [video:] tag
* fixes: html cleaning, remove empty tags
* fixed: efficient nofollow and noindex for non indexable pages
* enhanced: ignore list (tilde, hashes, DS_Store...)
* enhanced: automatic pageid
* enhanced: multiple body classes
* handle .tar, .gz and .tar extensions as downloadable
* wrap.php: allow one simple text line in links.txt, not converted as link
* added info from comment additions in playlist

1.8.0
* First stable release as W.R.A.P.
* Renamed browser* files to wrap
* split main code, functions and facebook auth
* fixes and cosmetic changes
* Initial fork of "browser" project, renamed W.R.A.P.
* A php app with huge bunch of files, tools, libraries, codes,
  developed between 2000 and 2013 under the too generic name "browser"
