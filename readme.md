<p align="center"><img src="https://laravel.com/assets/img/components/logo-laravel.svg"></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/d/total.svg" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/v/stable.svg" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/license.svg" alt="License"></a>
</p>

## Laravel memory leak example

It is normal to loop and process data in script, I found some weird memory leak in Laravel, and wonder how this happened. I have some workaround to prevent the memory leak, but it can't solve the root cause, so I provide some cases to demo the memory leak, hope someone can solve this issue.

### Install

```
$ composer install
```

```
$ php artisan migrate
```

### Data seeding

```
$ php artisan leak_test_data
```

### Usage

#### Case 1

This first case demo a simple loop cause the memory leak:

```
$ php artisan leak_test leak
```

Some details:

```php
$albums = Album::take(10000)->get();
foreach ($albums as $album) {
    $songs = $album->songs; // cause memory leak
}
```

And we can see the memory goes up and never come back:

```
...
550 start
#executions = 550 - mem: 14799640
550 end
551 start
#executions = 551 - mem: 14801728
551 end
552 start
#executions = 552 - mem: 14803800
552 end
553 start
#executions = 553 - mem: 14805872
553 end
554 start
#executions = 554 - mem: 14807944
554 end
555 start
#executions = 555 - mem: 14810024
555 end
556 start
#executions = 556 - mem: 14812112
556 end
557 start
#executions = 557 - mem: 14814192
557 end
...
```

I know there is N+1 query in it, but simple loop with simple qurey should not cause memory leak, it happend in Laravel.

#### Case 2

This second case demo a simple loop with N+1 query, but no memory leak:

```
$ php artisan leak_test no_leak
```

Some details:

```php
$albums = Album::take(10000)->get();
foreach ($albums as $album) {
    $songs = $album->songs()->get(); // why this don't cause the memory leak?
}
```

We can see the memory usage is stable:

```
...
439 start
#executions = 439 - mem: 13659472
439 end
440 start
#executions = 440 - mem: 13659456
440 end
441 start
#executions = 441 - mem: 13659464
441 end
442 start
#executions = 442 - mem: 13659456
442 end
443 start
#executions = 443 - mem: 13659456
443 end
444 start
#executions = 444 - mem: 13659464
444 end
445 start
#executions = 445 - mem: 13659456
445 end
446 start
#executions = 446 - mem: 13659464
446 end
...
```

This is reasonable, although there is N+1 query, but should not cause memory leak.

#### Case 3

Third case demo a simple loop and use "with" to solve N+1 query.

```
$ php artisan leak_test leak_solve_by_with
```

Some details:

```php
$albums = Album::with(['songs'])->take(10000)->get();
foreach ($albums as $album) {
    $songs = $album->songs; // cause memory leak
}
```

We can see the memory usage is always same:

```
...
1239 start
#executions = 1239 - mem: 16015944
1239 end
1240 start
#executions = 1240 - mem: 16015944
1240 end
1241 start
#executions = 1241 - mem: 16015944
1241 end
1242 start
#executions = 1242 - mem: 16015944
1242 end
1243 start
#executions = 1243 - mem: 16015944
1243 end
1244 start
#executions = 1244 - mem: 16015944
1244 end
1245 start
#executions = 1245 - mem: 16015944
1245 end
1246 start
#executions = 1246 - mem: 16015944
1246 end
1247 start
#executions = 1247 - mem: 16015944
1247 end
...
```

This is trivial, this solve the N+1 query, get all the data first, and Laravel use the data and no need to query again and again, so if we can get all the data into the memory, the script will excute perfectly.

#### Case 4

This case demo a common case when we write OOP, we use use some method in model, and model will get the necessary data to proceed the work.

```
$ php artisan leak_test leak_weird
```

Some details:

```php
$albums = Album::take(10000)->get();
foreach ($albums as $album) {
    $songs = $album->processSomethingToReturn();
}

// in Album.php
public function processSomethingToReturn()
{
    $songs = $this->songs; // this cause memory leak
    // do something here...
    return $songs;
}
```

And we can see the memory goes up and never come back:

```
...
453 start
#executions = 453 - mem: 14598224
453 end
454 start
#executions = 454 - mem: 14600296
454 end
455 start
#executions = 455 - mem: 14602376
455 end
456 start
#executions = 456 - mem: 14604456
456 end
457 start
#executions = 457 - mem: 14606528
457 end
458 start
#executions = 458 - mem: 14608616
458 end
459 start
#executions = 459 - mem: 14610688
459 end
460 start
#executions = 460 - mem: 14612760
460 end
461 start
#executions = 461 - mem: 14614840
461 end
...
```

This is common to write some method for "Encapsulation", we don't need to know the detail, just call the method to do what we want. But in this case we got memory leak.

#### Case 5

In this final case we use walkaround to solve the memory leak by "with" magic:

```
$ php artisan leak_test leak_solve_by_with_weird
```

Some details:

```php
$albums = Album::with(['songs'])->take(10000)->get();
foreach ($albums as $album) {
    $songs = $album->processSomethingToReturn();
}

// in Album.php
public function processSomethingToReturn()
{
    $songs = $this->songs; // this cause memory leak
    // do something here...
    return $songs;
}
```

We can see the memory usage is always the same:

```
...
1240 start
#executions = 1240 - mem: 16015952
1240 end
1241 start
#executions = 1241 - mem: 16015952
1241 end
1242 start
#executions = 1242 - mem: 16015952
1242 end
1243 start
#executions = 1243 - mem: 16015952
1243 end
1244 start
#executions = 1244 - mem: 16015952
1244 end
1245 start
#executions = 1245 - mem: 16015952
1245 end
1246 start
#executions = 1246 - mem: 16015952
1246 end
1247 start
#executions = 1247 - mem: 16015952
1247 end
1248 start
#executions = 1248 - mem: 16015952
1248 end
...
```

This walkaround solve the memory leak, but really wired, in "Encapsulation" principle, we shoud not to know the detail of method, so we use `with(['songs'])` in advence is really wired, this should not happend when we write code.

Apparently we shold solve the root cause of memory leak. Why `$this->songs` in loop cause memory leak but `$this->songs()->get()` not?


