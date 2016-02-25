# Screaming Liquid Tiger

Simple script to automatically generate valid RSS and Atom feeds from a list of media files in the same folder. Written in PHP to enable use even on the cheapest shared host.

I wrote this because I wanted an easy way to use [Overcast](https://overcast.fm/) to listen to my audiobooks. Podcast players are ideal applications for this, contrary to generic media players. It is also trivial to be adapted to other uses and file types.

* Automatically gets media files from same folder, no fiddling with a GUI
* Automatically sets file modification date
* Automatically sets per-file MIME type
* Automatically sets file size
* Generates valid RSS and Atom feeds for maximum compatibility
* Automatically extracts tag information when `mediainfo` is installed

## Installation and Usage

* Upload `index.php` file to a folder on a web server running PHP 5.4 or higher.
* Edit the configuration variables if needed and upload media files of any kind.
* Optionally make sure `mediainfo` is installed. Set the `$mediainfo_check` variable to `true` and follow the instructions displayed. If no instructions show but your feed appears, it already works.

For personal podcasting use, those should obviously be audio files but you can use it with any kind of file.

For easier upgrades, you can now use a `config.php` file (included) to set your options. Just rename `config.php.default` to `config.php` and make your adjustments. The script will autodetect if it is present. If not, it will fall back to its included defaults.

## How to Create Your Own Audiobooks

There are several approaches but the one I like best is converting CDs or audio files to a 'Bookmarkable MP4 File', most commonly spotted by its extension `.m4b`. Here's a quick list of software you can use.

For Mac OS X, take a look at [Audiobook Builder](http://www.splasm.com/audiobookbuilder/). It's a fantastic and inexpensive piece of software. It's what I use.

For Windows, I hear good things about  [Chapter and Verse](http://lodensoftware.com/chapter-and-verse/). You'll need both the application and iTunes for it to work.

For Linux, there are two older projects worth mentioning: [m4baker](https://github.com/crabmanX/m4baker) and [zak](https://code.google.com/p/zak/). If those won't do, you could always script the hell out of `ffmpeg`, `mp4v2` and `MP4tools`.

## Donation

In case you feel particularly generous today, you can buy me a coffee. That would really make my day. Kindness of strangers and all that. If you can't or won't, no hard feelings.

<https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=5AS5YZU4X5LV2>

## Screenshot

Here's what an audiobook may look like while playing in Overcast. Nice, isn't it?

![Screenshot](https://raw.githubusercontent.com/herrbischoff/screaming-liquid-tiger/master/assets/screenshot.jpg)

## Changelog

### 0.3.0

* Refactoring of feed generation code.
* Moved config file to template.

### 0.2.2

* Enabled external config file `config.php`.

### 0.2.1

* Exchanged `ffmpeg` for `mediainfo`, as image support in podcasting clients is generally limited and `mediainfo` generates faster and more reliable output.
* Set `$mediainfo_check` variable to `false` as default

### 0.2.0

* Added optional tag and artwork reading capability through `ffmpeg`
* Added optional feed image output
* Added optional duration tag

### 0.1.2

* Added .gitignore
* Proper array alignment
* Added FLAC extension and MIME type
* Exchanged fileinfo for manual array of MIME values
* Normalized PHP echo tags
* Implemented XML header fix for PHP 7

### 0.1.1

* Some restructuring and clean up.
* More code comments.

### 0.1.0

* Initial release.

## License

The MIT Licence

Copyright © 2015 Marcel Bischoff

Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the "Software"),
to deal in the Software without restriction, including without limitation
the rights to use, copy, modify, merge, publish, distribute, sublicense,
and/or sell copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included
in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM,
DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE
OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
