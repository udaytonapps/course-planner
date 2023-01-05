<?php

/** A list of all terms */
$TERMS = array(
    202310 => 'Spring 2023',

    202280 => 'Fall 2022',
    2022533 => 'Summer 2022 - Full Third Term',
    2022531 => 'Summer 2022 - First Session',
    2022532 => 'Summer 2022 - Second Session',
    202210 => 'Spring 2022',

    202180 => 'Fall 2021',
    2021533 => 'Summer 2021 - Full Third Term',
    2021531 => 'Summer 2021 - First Session',
    2021532 => 'Summer 2021 - Second Session',
    202110 => 'Spring 2021',

    202080 => 'Fall 2020',
);

/** The default term */
$DEFAULT_TERM = $TERMS[202080];

/** Retrieves the text representation of the term name like 'Fall 2023' */
function getTerm(int $term)
{
    global $TERMS, $DEFAULT_TERM;
    if (isset($TERMS[$term])) {
        return $TERMS[$term];
    } else {
        return $DEFAULT_TERM;
    }
}

/** Renders a list of options for a select dropdown based on the array of terms above */
function renderTermOptions(string $activeTerm = null)
{
    global $TERMS;

    foreach ($TERMS as $key => $value) {
?>
        <option value="<?= $key; ?>" <?= isset($activeTerm) && $activeTerm == $key ? "selected" : "" ?>><?= $value ?></option>
<?php
    }
}
