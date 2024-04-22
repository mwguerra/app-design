<?php

it('can generate a test file from a model name', function () {
    $model = [
        'name' => 'book',
        'relationships' => [
            ['type' => 'hasOne', 'model' => 'author', 'functionName' => 'author'],
            ['type' => 'hasMany', 'model' => 'review', 'functionName' => 'reviews'],
            ['type' => 'belongsTo', 'model' => 'series', 'functionName' => 'series'],
            ['type' => 'belongsToMany', 'model' => 'tag', 'functionName' => 'tags'],
            ['type' => 'morphMany', 'model' => 'comment', 'functionName' => 'comments']
        ]
    ];

    $testGenerator = new MWGuerra\AppDesign\Generators\TestGenerator(
        model: $model,
        dryRun: true
    );

    $generatedContent = $testGenerator->generate();

    $expectedContent = <<<'PHP'
<?php

// CRUD

it('can create a book', function () {
    $book = Book::factory()->create();
    $this->assertDatabaseHas('books', ['id' => $book->id]);
});

it('can read a book', function () {
    $book = Book::factory()->create();
    $foundBook = Book::find($book->id);
    expect($foundBook)->toEqual($book);
});

it('can update a book', function () {
    $book = Book::factory()->create();
    $book->update(['title' => 'Updated Title']);
    expect($book->title)->toEqual('Updated Title');
});

it('can delete a book', function () {
    $book = Book::factory()->create();
    $bookId = $book->id;
    $book->delete();
    $this->assertDatabaseMissing('books', ['id' => $bookId]);
});
// HasOne

it('can retrieve authors for a book', function () {
    $book = Book::factory()->hasAuthors(3)->create();
    expect($book->author)->toHaveCount(3);
});

it('can add a author to a book', function () {
    $book = Book::factory()->create();
    $author = Author::factory()->create(['book_id' => $book->id]);
    expect($book->author)->toHaveCount(1);
});
// HasMany

it('can retrieve reviews for a Book', function () {
    $book = Book::factory()->create();
    $reviews = Review::factory()->count(3)->create(['book_id' => $book->id]);

    expect($book->reviews)->toHaveCount(3);
});

it('can add a review to a Book', function () {
    $book = Book::factory()->create();
    $review = Review::factory()->make();

    $book->reviews()->save($review);

    expect($book->reviews)->toHaveCount(1);
});
// BelongsTo

it('can retrieve the series that owns the book', function () {
    $series = Series::factory()->hasBook(1)->create();
    $book = $series->series->first();
    expect($book->series)->toBeInstanceOf(Series::class);
});
// BelongsToMany

it('can attach tags to a Book', function () {
    $book = Book::factory()->create();
    $tags = Tag::factory()->count(2)->create();

    $book->tags()->attach($tags->pluck('id'));

    expect($book->tags)->toHaveCount(2);
});

it('can detach tags from a Book', function () {
    $book = Book::factory()->create();
    $tags = Tag::factory()->count(2)->create();
    $book->tags()->attach($tags->pluck('id'));

    $book->tags()->detach();

    expect($book->tags)->toHaveCount(0);
});
// MorphMany

it('can add a comment via polymorphic relation to a book', function () {
    $book = Book::factory()->create();
    $comment = Comment::factory()->create([
        'imageable_id' => $book->id,
        'imageable_type' => 'Book'
    ]);
    expect($book->comments)->toHaveCount(1);
});

it('can retrieve all comments via polymorphic relation from a book', function () {
    $book = Book::factory()->create();
    $comments = Comment::factory()->count(3)->create([
        'imageable_id' => $book->id,
        'imageable_type' => 'Book'
    ]);
    expect($book->comments)->toHaveCount(3);
});

it('can delete a comment via polymorphic relation from a book', function () {
    $book = Book::factory()->create();
    $comment = Comment::factory()->create([
        'imageable_id' => $book->id,
        'imageable_type' => 'Book'
    ]);
    $comment->delete();
    expect($book->comments)->toBeEmpty();
});
PHP;

    $this->assertStringContainsString(
        "it('can create a book', function () {",
        $generatedContent
    );

    $this->assertStringContainsString(
        "it('can retrieve authors for a book', function () {",
        $generatedContent
    );

    $this->assertStringContainsString(
        "it('can create a book', function () {",
        $generatedContent
    );

    $this->assertStringContainsString(
        $expectedContent,
        $generatedContent
    );
});


