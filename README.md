# Induce

Induce is a pretty interesting library for php, it actually implements what is
commonly called "reverse-regex expansion", but is actually induction. It isn't
really ready to be imported in composer now, but I'll eventually get around to
put it in shape for that purpose.

If you're interested in it and you wish I wrote some documentation, examples, and
allowed autoloading it... please let me know via the [issue tracker][issues].

## iWget

Induce also comes with a `wget` command line wrapper that allows it to download series of induced urls.

### Example

    iwget http://example.com/site/folder/picture[1..300].jpg

Will download `picture1.jpg`, `picture2.jpg`... up to `picture300.jpg`

[issues]: https://github.com/zingus/induce/issues
