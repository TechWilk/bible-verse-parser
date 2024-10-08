<?php

require __DIR__ . '/../vendor/autoload.php';

use TechWilk\BibleVerseParser\BiblePassageParser;
use TechWilk\BibleVerseParser\Data\BibleStructure;
use TechWilk\BibleVerseParser\Enum\NumberingType;

$userText = trim($_POST['passage'] ?? '');
$userBibleStructure = trim($_POST['bible-structure'] ?? '');
$userIntegerInterpretation = trim($_POST['integer-interpretation'] ?? '');

$numberingType = match ($userIntegerInterpretation) {
    'chronological' => NumberingType::Chronological,
    default => NumberingType::USFM,
};

$parser = match ($userBibleStructure) {
    "catholic" => new BiblePassageParser(
        BibleStructure::getBibleStructureCatholic(),
        numberingType: $numberingType,
        lettersAreFragments: false,
    ),
    default => new BiblePassageParser(
        BibleStructure::getBibleStructure(),
        numberingType: $numberingType,
    ),
};

$error = '';
$passages = [];
try {
	if ($userText) {
		$passages = $parser->parse($userText);
	}
} catch (Exception $e) {
	$error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Bible Verse Parser demo</title>
<style>
body {
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
}
</style>
</head>
<body>

<h1>Bible Verse Parser demo</h1>

<form method="post">
<label>
Passages:
<input type="text" name="passage" value="<?= htmlentities($userText, ENT_QUOTES) ?>" />
</label>
<label>
Bible Structure:
<select name="bible-structure">
    <option value="protestant" <?= $userBibleStructure === 'protestant' ? 'selected' : '' ?>>Protestant</option>
    <option value="catholic" <?= $userBibleStructure === 'catholic' ? 'selected' : '' ?>>Catholic</option>
</select>
</label>
<label>
Interpret integers as:
<select name="integer-interpretation">
    <option value="usfm" <?= $userIntegerInterpretation === 'usfm' ? 'selected' : '' ?>>USFM</option>
    <option value="chronological" <?= $userIntegerInterpretation === 'chronological' ? 'selected' : '' ?>>Chronological</option>
</select>
</label>
<input type="submit" value="Parse" />
</form>

<?php if ($error): ?>
<p><?= htmlentities($error) ?></p>
<p>If this is due to a missing abbreviation / common typo, unsupported format, or bug then please <a href="https://github.com/TechWilk/bible-verse-parser/issues">open an issue</a>.<p>
<?php endif ?>

<?php if (!$userText): ?>
<p>Enter a passage (or multiple), such as "John 3:16-18, 20-21"</p>
<?php endif ?>

<?php if ($passages): ?>
<h2>Passages:</h2>

<h3>Shorthand</h3>
<?php foreach ($passages as $passage): ?>
<p><?= htmlentities((string) $passage) ?></p>
<?php endforeach ?>

<h3>Longhand</h3>
<?php foreach ($passages as $passage): ?>
<p>
	<?= htmlentities((string) $passage->from()) ?>
	to
	<?= htmlentities((string) $passage->to()) ?>
</p>
<?php endforeach ?>

<h3>Integer (<a href="https://ubsicap.github.io/usfm/linking/index.html#general-syntax">USFM</a> numbering)</h3>
<?php foreach ($passages as $passage): ?>
<p>
	<?= htmlentities((string) $passage->from()->integerNotationUSFM()) ?>
	to
	<?= htmlentities((string) $passage->to()->integerNotationUSFM()) ?>
</p>
<?php endforeach ?>

<h3>Integer (chronological numbering)</h3>
<?php foreach ($passages as $passage): ?>
<p>
	<?= htmlentities((string) $passage->from()->integerNotationChronological()) ?>
	to
	<?= htmlentities((string) $passage->to()->integerNotationChronological()) ?>
</p>
<?php endforeach ?>


<h3><a href="https://ubsicap.github.io/usfm/linking/index.html#general-syntax">USFM</a> reference</h3>
<?php foreach ($passages as $passage): ?>
<p>
	<?= htmlentities($passage->formatAsUSFM()) ?>
</p>
<?php endforeach ?>

<h3>URL-safe USFM-style reference</h3>
<?php foreach ($passages as $passage): ?>
<p>
	<?= htmlentities($passage->formatAsURLSafeUSFM()) ?>
</p>
<?php endforeach ?>
<?php endif ?>

<footer>
<small>
	View code on
	<a href="https://github.com/techwilk/bible-verse-parser">GitHub</a>
	or
	<a href="https://git.sr.ht/~techwilk/bible-verse-parser">Sourcehut</a>
</small>
</footer>
</body>
</html>
