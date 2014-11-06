Conformance-Software
====================

This package, for now, only provides the Web-FE of MPEG-DASH/DASH-IF ISO-Segment Validator for Windows platforms.

Tested for:

1) Chrome for Window, Linux, and OS X.
2) IE.
3) Firefox
4) Safari for Windows



Features

The web front-end exposes all features supported by MPEG-DASH/DASH-IF ISO Segment Validator, except:

Dynamic MPDs.
Sub-representations.
(list to be reviewed and completed)....

## Instillation on Ubuntu 14.04 ##

Install pre-requisites

    sudo apt-get install git subversion apache2 libapache2-mod-php5 php5 php5-curl ant default-jdk build-essential libc6-dev-i386 gcc-4.8-multilib g++-4.8-multilib

Check out DASH-IF conformance checker

    git clone https://github.com/jeremypoulter/Conformance-Software.git
    sudo mv Conformance-Software /var/www/html
    sudo chown www-data:www-data /var/www/html/Conformance-Software/webfe/temp

Check out DASH MPD validator and build

    git clone https://github.com/jeremypoulter/Conformance-and-reference-source.git
    cd Conformance-and-reference-source/conformance/ISOSegmentValidator/public/linux
    mkdir obj
    make
    cp bin/ValidateMP4.exe /var/www/html/Conformance-Software/webfe/validatemp4-linux
    chmod +x /var/www/html/Conformance-Software/webfe/validatemp4-linux

Make sure the mpdvalidator(s) is built to not cause delays when running

    cd /var/www/html/Conformance-Software/webfe/mpdvalidator
    ant
    cd ../mpdvalidator2
    ant


