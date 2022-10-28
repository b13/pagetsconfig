# TYPO3 Extension "Pagetsconfig"

## About this extension

TYPO3's out of the box configuration options to change the way editors interact with content are impressive and cover 
almost all use cases we encounter in our daily work. Changing backend forms for example for content element types globally
or for a specific content element type is a breeze — you can just override global TCA settings. Changing some other 
elements, like a field label, hiding a specific field in a specific content element type within just parts of your 
TYPO3 instance (whether for one of your page trees or a part of a larger page tree) is easy to do using Page TsConfig.

Sometimes, though, you might want some more options than ship with TYPO3 core. At least we do from time to time. 
For advanced use cases we created (and use) this extension. 

This extension enables advanced configuration options using Page TsConfig for special use cases.

## Use cases for this extension

### Changing configuration for inline fields

Depending on your frontend design your "Element Component 1" might feature an image with a full palette of fields for 
meta data - alt text, title text, a link etc. For parts of your page tree, though, the element uses a different frontend
template and some of these fields are ignored. Your editor might be confused about a "link" field, that does not do
anything. Here's how to change a `showitem` configuration for an inline field:

```
TCEFORM.tt_content {
  image.types.component-1.config.overrideChildTca.types.2.showitem = --palette--;;basicImageoverlayPalette, --palette--;;filePalette
} 
```

### Changing crop variants using PageTS

We maintain TYPO3 instances with multiple websites using a common set of templates and content element types. We normally
set fixed cropVariants for all image assets an editor can use in a specific element if the design calls for a specific
aspect ratio; often times more than one, depending on the design.

Sometimes we have a set of teaser elements in such a template package, the default ratio for this element's image is 4:3, but 
for some reason two out of your 70 websites built upon that template package use a design slightly different and want to
have a 16:9 image ratio for that specific element.

This extension enables the use of Page TsConfig to alter available cropVariant settings:

```
TCEFORM.tt_content.image.types.my_example_teaser.config.overrideChildTca.columns.crop.config.cropVariants {
  default {
    allowedAspectRatios.default.title = 16:9
    allowedAspectRatios.default.value = 1.77
    selectedRatio = default
  }
}
```

Depending on our use case we can change a setting for an existing named cropVariant, or add a new one — sometimes there's
no cropVariant set globally to begin with (and we set everything using PageTS), sometimes there's the need to change on 
part.

This is also possible for inline records, their values and their respective settings (`tx_listelements_list` being an
inline element created using EXT:listelements):

```
TCEFORM.tt_content {
  tx_listelements_list.types.timeline.config.overrideChildTca.columns.images.config.overrideChildTca.columns.crop.config.cropVariants {
    default.title = My new title
    allowedAspectRatios.1:1.title = 1:1
    allowedAspectRatios.1:1.value = 1
    selectedRatio = 1:1
  }
}
```

### Changing palette configuration

Sometimes it makes sense to rearrange fields in the backend within a palette for one of your page trees, for example because it 
helps the editor to better recognize the connection between the backend form and the frontend design. It is possible
using Page TsConfig to change settings for palettes:

```
# move the "media" field from example-palette-2 to example-palette-1
TCEFORM.tt_content {
  palettes.types.startpage-header.startpage-header-media.label = Custom palette title
  palettes.types.startpage-header.startpage-header-media.showitem = image, --linebreak--, media
  # an empty palette will not be shown at all
  palettes.types.startpage-header.startpage-header-media-desktop.showitem =
}
```

Important note: While you can mess with the order of fields that way, you cannot add a field entirely. The TCA `showitem`
configuration determines which fields are available for your backend form; you might need to add a field globally, remove
it globally and enable it for a specific page tree depending on your use case.

## When to not use this extension

If you want to change some setting for some backend form globally, always put your configuration in `TCA/Overrides`!

## Credits

This extension was created by Benni Mack way back in 2013, and repurposed 2022 by David Steeb for [b13 GmbH, Stuttgart](https://b13.com).

[Find more TYPO3 extensions we have developed](https://b13.com/useful-typo3-extensions-from-b13-to-you) that help us deliver value in client projects. As part of the way we work, we focus on testing and best practices to ensure long-term performance, reliability, and results in all our code.
