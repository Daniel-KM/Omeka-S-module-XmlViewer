XML Viewer (module for Omeka S)
===============================

> __New versions of this module and support for Omeka S version 3.0 and above
> are available on [GitLab], which seems to respect users and privacy better
> than the previous repository.__

[XML Viewer] is a module for [Omeka S] that integrates a simple viewer for XML.
Currently, only the TEI is available by default.

TEI is the Text Encoding Initiative, a standard used for the representation of
the manuscripts and any other texts in a digital format. It uses the html/css/js/xsl
of [Œuvre/Teinte].


Installation
------------

See general end user documentation for [installing a module].

First, if wanted, install the optional modules [Generic].

The module uses an external library, [Œuvre/Teinte], so use the release zip to
install it, or use and init the source.

* From the zip

Download the last release [XmlViewer.zip] from the list of releases (the master
does not contain the dependency), and uncompress it in the `modules` directory.

* From the source and for development:

If the module was installed from the source, rename the name of the folder of
the module to `XmlViewer`, and go to the root module, and run:

```sh
composer install --no-dev
```


Usage
-----

### Display

The XML is automatically displayed.

### Rendering

If you want to modify the rendering, update the xsl or the css in the view
assets.

You can update  the list of the xml media types too in the file `data/xml/mediatypes.php`.

### XML media-type

The media type is the short string like `type/subtype` that identify the type of
a file from its real content, not from the extension (as in Apple/Windows). It
is managed by the Internet Assigned Numbers Authority (IANA) and is a
generalisation of the old mime-type (Multipurpose Internet Mail Extensions),
that was designed to identify files attached to mails. Nowaday, it's
standardized in the RFC 6838 of IETF.

For XML, Omeka considers that all imported xml files as `application/xml` or
`text/xml`, depending on the server. But the main xml formats use a more precise
value, for example `application/xml-tei` for TEI, or `application/vnd.mei+xml`
for XML-MEI, or `application/vnd.mets+xml` for METS, or `image/svg+xml` for SVG.
There may be old values too. For example, the value for TEI before 2011 was
`application/vnd.tei+xml`, because it wasn't registered in the [iana list].

Furthermore, some xml files may be zipped, like the Open Document one (first
official standard office documents used by LibreOffice and many other office
suites), or MusicXML `application/vnd.recordare.musicxml`. They may be
identified by the server and Omeka as zip `application/zip`, but they are mainly
XML files.

So, to simplify rendering and to increase speed, it is recommended to normalize
them first, else it will be checked each time a file is rendered. New xml files
are normally well identified. To normalize old ones, you need the module
[Bulk Edit] and to use the batch edit feature in item/browse and media/browse.

### MusicXML and XML-MEI

There is a special module for MusicXml and XML-MEI, [Verovio], that render the
xml files with a score player.


Warning
-------

Use it at your own risk.

It’s always recommended to backup your files and your databases and to check
your archives regularly so you can roll back if needed.


Troubleshooting
---------------

See online issues on the [module issues] page on GitLab.


License
-------

This module is published under the [CeCILL v2.1] license, compatible with
[GNU/GPL] and approved by [FSF] and [OSI].

In consideration of access to the source code and the rights to copy, modify and
redistribute granted by the license, users are provided only with a limited
warranty and the software’s author, the holder of the economic rights, and the
successive licensors only have limited liability.

In this respect, the risks associated with loading, using, modifying and/or
developing or reproducing the software by the user are brought to the user’s
attention, given its Free Software status, which may make it complicated to use,
with the result that its use is reserved for developers and experienced
professionals having in-depth computer knowledge. Users are therefore encouraged
to load and test the suitability of the software as regards their requirements
in conditions enabling the security of their systems and/or data to be ensured
and, more generally, to use and operate it in the same conditions of security.
This Agreement may be freely reproduced and published, provided it is not
altered, and that no provisions are either added or removed herefrom.

[Œuvre/Teinte] is published under [LGPL].


Copyright
---------

[Œuvre/Teinte]:

* Copyright 2005-2021, ajlsm.com, cyberthèses, Fréderic Glorieux & al. (see files).

Module [Xml Viewer] for Omeka S:

* Copyright Daniel Berthereau, 2019-2021

First version of this module was built for the future digital library of [Association Valentin Haüy],
with the help of the Observatoire de la vie littéraire [OBVIL] of [Sorbonne Université].
Some code was integrated from the module [Next].


[XML Viewer]: https://gitlab.com/Daniel-KM/Omeka-S-module-XmlViewer
[Œuvre/Teinte]: https://github.com/oeuvres/teinte
[Omeka S]: https://omeka.org/s
[XmlViewer.zip]: https://gitlab.com/Daniel-KM/Omeka-S-module-XmlViewer/-/releases
[Installing a module]: http://dev.omeka.org/docs/s/user-manual/modules/#installing-modules
[Generic]: https://gitlab.com/Daniel-KM/Omeka-S-module-Generic
[module issues]: https://gitlab.com/Daniel-KM/Omeka-S-module-XmlViewer/-/issues
[Verovio Viewer]: https://gitlab.com/Daniel-KM/Omeka-S-module-Verovio
[Bulk Edit]: https://gitlab.com/Daniel-KM/Omeka-S-module-BulkEdit
[Next]: https://gitlab.com/Daniel-KM/Omeka-S-module-Next
[iana list]: https://www.iana.org/assignments/media-types/media-types.xhtml
[CeCILL v2.1]: https://www.cecill.info/licences/Licence_CeCILL_V2.1-en.html
[GNU/GPL]: https://www.gnu.org/licenses/gpl-3.0.html
[FSF]: https://www.fsf.org
[OSI]: http://opensource.org
[LGPL]: http://www.gnu.org/licenses/lgpl.html
[GitLab]: https://gitlab.com/Daniel-KM
[Association Valentin Haüy]: https://avh.asso.fr
[OBVIL]: https://obvil.sorbonne-universite.fr
[Sorbonne Université]: https://www.sorbonne-universite.fr
[Daniel-KM]: https://gitlab.com/Daniel-KM "Daniel Berthereau"
