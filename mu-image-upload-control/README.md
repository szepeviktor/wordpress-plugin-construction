# Image upload control

Help users to keep image file names clean and descriptive.

### Recommended file names

- post-title-and-what-is-on-the-picture.jpg
- conference2016 report cover.png
- Francis profile blackandwhite.jpg

### Help on every check

| Old-style image name | Clean and descriptive name | Comment |
| -------------------- | -------------------------- | ------- |
| me_and_jane_home.jpg | me-and-jane-home.jpg | Replace under_scores with dash-es |
| logo.png | ACME-company-logo.png | Keep file names longer (10+) and meaningful |
| nettie..50years.jpg | nettie.50years.jpg | Avoid multiple dots |
| DSC1234.jpg | green-tree-Ohio.jpg | You can make up smarter names than your camera |
| -nice-memories-2004.jpg | 2004-nice-memories.jpg | Begin with a letter or a digit |
| IMG-000001.png | First image of the year-2016.jpg | You are much smarter than a computer program |
| Screen-Shot-2672345768.jpg | how to use PS small.jpg | Screenshots are happier with meaningful names |
| Christie-shopping-1500x300.jpg | Christie-shopping-header.jpg | Image dimensions may mess up your WordPress site |
| CAD-drawing.**tif** | CAD-drawing.png | Use JPEG for photorealistic images and PNG for drawings |
| Katie-sunshine-raw.jpg | Katie-sunshine-HD.jpg | Downsize your images to FullHD |
| dot 16-16 of the year.png | dot of the year visible.png | It is retina age, upload at least 32×32 images |

@TODO Add "codes" check.
@TODO Add ".php" check.

*Check translation consistency:*

```bash
colordiff image-upload-control.php image-upload-control-hu.php
```
