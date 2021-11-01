# Size

## Width
Generate url for image with the new width of the image. With is in pixels.

<!-- \Reimage\Test\TestCase\Docs\FeaturesTest::testWidth -->
```php
$reimage = new Reimage();
$url = $reimage->createUrl('/my_image.jpg', [Reimage::WIDTH => 300]);
//Result: $url = '/my_image_HDTOSX.jpg?w=300&s=jUcdHD'
```

## Height
Generate url for image with the new height of the image. Height is in pixels.

<!-- \Reimage\Test\TestCase\Docs\FeaturesTest::testHeight -->
```php
$reimage = new Reimage();
$url = $reimage->createUrl('/my_image.jpg', [Reimage::HEIGHT => 200]);
//Result: $url = '/my_image_s84fh2.jpg?h=200&s=Mq5J7t'
```

## Width & Height
You can set both dimensions. New image will have specified dimension but little bite of image will be missing.

<!-- \Reimage\Test\TestCase\Docs\FeaturesTest::testWidthAndHeight -->
```php
$reimage = new Reimage();
$url = $reimage->createUrl('/my_image.jpg', [Reimage::WIDTH => 300, Reimage::HEIGHT => 300]);
//Result: $url = '/my_image_a6z-EC.jpg?w=300&h=300&s=toUNhf'
```
