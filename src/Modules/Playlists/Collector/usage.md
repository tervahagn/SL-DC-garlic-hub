# How to use Collector 

```php
$playlistBuilder = $playlistBuilderFactory->createBuilder($playerEntity);
$playlist = $playlistBuilder->buildPlaylist();

$items     = $playlist->getItems();
$prefetch  = $playlist->getPrefetch();
$exclusive = $playlist->getExclusive();
```
