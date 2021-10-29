# Simple examples

## Create url for image with defined size
<!--- @see \Reimage\Test\TestCase\Docs\ExamplesTest::testSimplestUsage() -->
```php
$reimage = new Reimage();
$url = $reimage->createUrl('/my_image.jpg', [Reimage::WIDTH => 200, Reimage::HEIGHT => 150, Reimage::GREYSCALE => 1]);
//Result: $url = '/my_image_1KJYc9.jpg?w=200&h=150&grey=1&s=u7OX3-'
```

## Create url for image with defined size in grayscale
<!--- \Reimage\Test\TestCase\Docs\ExamplesTest::testSimpleGreyscale -->
```php
$reimage = new Reimage();
$url = $reimage->createUrl('/my_image.jpg', [Reimage::WIDTH => 200, Reimage::HEIGHT => 150, Reimage::GREYSCALE => 1]);
//Result: $url = '/my_image_1KJYc9.jpg?w=200&h=150&grey=1&s=u7OX3-'
```
