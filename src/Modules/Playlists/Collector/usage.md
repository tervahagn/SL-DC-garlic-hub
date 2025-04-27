# How to use Collcetor 

```php
$playlistBuilder = $playlistBuilderFactory->createBuilder($playlistEntity);
$playlist = $playlistBuilder->buildPlaylist();

$items     = $playlist->getItems();
$prefetch  = $playlist->getPrefetch();
$exclusive = $playlist->getExclusive();
```
