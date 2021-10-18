![Reimage logo](docs/images/logo.png)

# About Reimage
Create modified images from original image on the fly.

**Currently work in progress**

## Installing via Composer
```bash
composer require reimage/reimage
```

## How does it work?
TODO

## Requirements
The Reimage library has the following requirements:
 - PHP 7.3 or PHP 7.4 or PHP 8.0
 - One of the Image processing libraries:
   - [imagine/imagine](https://github.com/avalanche123/Imagine) (PHP 5.3+, Imagick 6.2.9+, GD 2.0+, Gmagick)
   - [intervention/image](https://github.com/Intervention/image) (PHP 5.4+, Imagick 6.5.7+, GD 2.0+)

## Optional libraries
File system libraries:
- [league/flysystem](https://flysystem.thephpleague.com/) v2.0+ (AWS S3, FTP, SFTP, in memory)

## Basic usage
<!--- \Reimage\Test\TestCase\Docs\HomepageTest::testSimplestUsage -->
```php
$reimage = new Reimage();
$url = $reimage->createUrl('/my_image.jpg', [Reimage::WIDTH => 300, Reimage::HEIGHT => 200]);
//Result: $url = '/my_image_fpA63N.jpg?w=300&h=200&s=4L1CZi'
```
