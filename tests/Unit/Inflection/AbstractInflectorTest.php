<?php

namespace Tests\Unit\Inflection;

use Eufony\ORM\Inflection\InflectorInterface;
use PHPUnit\Framework\TestCase;

/**
 * Provides an abstract `InflectorInterface` implementation tester.
 */
abstract class AbstractInflectorTest extends TestCase {

    /**
     * The `InflectorInterface` implementation to test.
     *
     * @var \Eufony\ORM\Inflection\InflectorInterface $inflector
     */
    protected InflectorInterface $inflector;

    /**
     * Returns a new instance of an `InflectorInterface` implementation to
     * test.
     *
     * @return \Eufony\ORM\Inflection\InflectorInterface
     */
    abstract public function getInflector(): InflectorInterface;

    /**
     * Data provider for pascal, snake, and camel-cased strings.
     *
     * @return string[][]
     */
    public function cases(): array {
        return [
            ["FooBar", "foo_bar", "fooBar"],
            ["", "", ""]
        ];
    }

    /**
     * Data provider for words with and their plural forms.
     *
     * @return string[][]
     */
    public function words(): array {
        return [
            ["", ""],
            ["ability", "abilities"],
            ["abuse", "abuses"],
            ["address", "addresses"],
            ["advice", "advice"],
            ["agency", "agencies"],
            ["aircraft", "aircraft"],
            ["alias", "aliases"],
            ["alumnus", "alumni"],
            ["analysis", "analyses"],
            ["arch", "arches"],
            ["archive", "archives"],
            ["art", "art"],
            ["atlas", "atlases"],
            ["audio", "audio"],
            ["avalanche", "avalanches"],
            ["axe", "axes"],
            ["baby", "babies"],
            ["bacillus", "bacilli"],
            ["bacterium", "bacteria"],
            ["baggage", "baggage"],
            ["basis", "bases"],
            ["bison", "bison"],
            ["box", "boxes"],
            ["bream", "bream"],
            ["breeches", "breeches"],
            ["britches", "britches"],
            ["buffalo", "buffalo"],
            ["bureau", "bureaus"],
            ["bus", "buses"],
            ["butter", "butter"],
            ["cache", "caches"],
            ["cactus", "cacti"],
            ["cafe", "cafes"],
            ["calf", "calves"],
            ["canvas", "canvases"],
            ["carp", "carp"],
            ["case", "cases"],
            ["category", "categories"],
            ["chassis", "chassis"],
            ["chateau", "chateaux"],
            ["cherry", "cherries"],
            ["child", "children"],
            ["church", "churches"],
            ["circus", "circuses"],
            ["city", "cities"],
            ["clippers", "clippers"],
            ["clothes", "clothes"],
            ["clothing", "clothing"],
            ["coal", "coal"],
            ["cod", "cod"],
            ["coitus", "coitus"],
            ["comment", "comments"],
            ["compensation", "compensation"],
            ["contretemps", "contretemps"],
            ["cookie", "cookies"],
            ["copy", "copies"],
            ["corps", "corps"],
            ["cotton", "cotton"],
            ["cow", "cows"],
            ["crisis", "crises"],
            ["criterion", "criteria"],
            ["currency", "currencies"],
            ["curriculum", "curricula"],
            ["curve", "curves"],
            ["data", "data"],
            ["database", "databases"],
            ["day", "days"],
            ["debris", "debris"],
            ["deer", "deer"],
            ["demo", "demos"],
            ["diabetes", "diabetes"],
            ["diagnosis", "diagnoses"],
            ["dictionary", "dictionaries"],
            ["dive", "dives"],
            ["domino", "dominoes"],
            ["dwarf", "dwarves"],
            ["echo", "echoes"],
            ["edge", "edges"],
            ["education", "education"],
            ["eland", "eland"],
            ["elf", "elves"],
            ["elf", "elves"],
            ["elk", "elk"],
            ["emoji", "emoji"],
            ["emphasis", "emphases"],
            ["energy", "energies"],
            ["equipment", "equipment"],
            ["evidence", "evidence"],
            ["experience", "experiences"],
            ["family", "families"],
            ["fax", "faxes"],
            ["feedback", "feedback"],
            ["fish", "fish"],
            ["fix", "fixes"],
            ["flounder", "flounder"],
            ["flour", "flour"],
            ["flush", "flushes"],
            ["fly", "flies"],
            ["focus", "foci"],
            ["foe", "foes"],
            ["food", "food"],
            ["foot", "feet"],
            ["fungus", "fungi"],
            ["furniture", "furniture"],
            ["gallows", "gallows"],
            ["gas", "gases"],
            ["genus", "genera"],
            ["glove", "gloves"],
            ["gold", "gold"],
            ["goose", "geese"],
            ["grave", "graves"],
            ["gulf", "gulfs"],
            ["half", "halves"],
            ["hardware", "hardware"],
            ["headquarters", "headquarters"],
            ["hero", "heroes"],
            ["herpes", "herpes"],
            ["hoax", "hoaxes"],
            ["homework", "homework"],
            ["horse", "horses"],
            ["house", "houses"],
            ["human", "humans"],
            ["identity", "identities"],
            ["impatience", "impatience"],
            ["index", "indices"],
            ["information", "information"],
            ["innings", "innings"],
            ["iris", "irises"],
            ["jeans", "jeans"],
            ["jedi", "jedi"],
            ["kitchenware", "kitchenware"],
            ["kiss", "kisses"],
            ["knife", "knives"],
            ["knowledge", "knowledge"],
            ["larva", "larvae"],
            ["leaf", "leaves"],
            ["leather", "leather"],
            ["lens", "lenses"],
            ["life", "lives"],
            ["loaf", "loaves"],
            ["louse", "lice"],
            ["love", "love"],
            ["luggage", "luggage"],
            ["mackerel", "mackerel"],
            ["maltese", "maltese"],
            ["man", "men"],
            ["management", "management"],
            ["matrix", "matrices"],
            ["medium", "media"],
            ["memorandum", "memoranda"],
            ["menu", "menus"],
            ["mess", "messes"],
            ["metadata", "metadata"],
            ["mews", "mews"],
            ["middleware", "middleware"],
            ["money", "money"],
            ["moose", "moose"],
            ["motto", "mottoes"],
            ["mouse", "mice"],
            ["move", "moves"],
            ["movie", "movies"],
            ["mumps", "mumps"],
            ["music", "music"],
            ["neurosis", "neuroses"],
            ["news", "news"],
            ["newsletter", "newsletters"],
            ["nexus", "nexus"],
            ["nucleus", "nuclei"],
            ["nutrition", "nutrition"],
            ["oasis", "oases"],
            ["octopus", "octopuses"],
            ["offspring", "offspring"],
            ["oil", "oil"],
            ["olive", "olives"],
            ["ox", "oxen"],
            ["pants", "pants"],
            ["pass", "passes"],
            ["passerby", "passersby"],
            ["patience", "patience"],
            ["pekingese", "pekingese"],
            ["person", "people"],
            ["perspective", "perspectives"],
            ["photo", "photos"],
            ["pincers", "pincers"],
            ["plankton", "plankton"],
            ["pliers", "pliers"],
            ["police", "police"],
            ["polish", "polish"],
            ["portfolio", "portfolios"],
            ["portuguese", "portuguese"],
            ["potato", "potatoes"],
            ["powerhouse", "powerhouses"],
            ["prize", "prizes"],
            ["proceedings", "proceedings"],
            ["process", "processes"],
            ["progress", "progress"],
            ["query", "queries"],
            ["quiz", "quizzes"],
            ["rabies", "rabies"],
            ["radius", "radii"],
            ["rain", "rain"],
            ["reflex", "reflexes"],
            ["research", "research"],
            ["rhinoceros", "rhinoceros"],
            ["rice", "rice"],
            ["roof", "roofs"],
            ["safe", "safes"],
            ["salesperson", "salespeople"],
            ["salmon", "salmon"],
            ["sand", "sand"],
            ["save", "saves"],
            ["scarf", "scarves"],
            ["scissors", "scissors"],
            ["scratch", "scratches"],
            ["search", "searches"],
            ["series", "series"],
            ["sex", "sexes"],
            ["shears", "shears"],
            ["sheep", "sheep"],
            ["shelf", "shelves"],
            ["shoe", "shoes"],
            ["siemens", "siemens"],
            ["silk", "silk"],
            ["sku", "skus"],
            ["slice", "slices"],
            ["sms", "sms"],
            ["soap", "soap"],
            ["software", "software"],
            ["spam", "spam"],
            ["species", "species"],
            ["splash", "splashes"],
            ["spokesman", "spokesmen"],
            ["spouse", "spouses"],
            ["spy", "spies"],
            ["stack", "stacks"],
            ["stadium", "stadia"],
            ["staff", "staff"],
            ["status", "statuses"],
            ["stimulus", "stimuli"],
            ["stitch", "stitches"],
            ["story", "stories"],
            ["sugar", "sugar"],
            ["swine", "swine"],
            ["switch", "switches"],
            ["syllabus", "syllabi"],
            ["talent", "talent"],
            ["tax", "taxes"],
            ["taxi", "taxis"],
            ["taxon", "taxa"],
            ["terminus", "termini"],
            ["testis", "testes"],
            ["thesis", "theses"],
            ["thief", "thieves"],
            ["tomato", "tomatoes"],
            ["tooth", "teeth"],
            ["toothpaste", "toothpaste"],
            ["tornado", "tornadoes"],
            ["traffic", "traffic"],
            ["travel", "travel"],
            ["trivia", "trivia"],
            ["trousers", "trousers"],
            ["trout", "trout"],
            ["try", "tries"],
            ["tuna", "tuna"],
            ["us", "us"],
            ["valve", "valves"],
            ["vertex", "vertices"],
            ["vinegar", "vinegar"],
            ["volcano", "volcanoes"],
            ["ware", "wares"],
            ["wash", "washes"],
            ["watch", "watches"],
            ["wave", "waves"],
            ["weather", "weather"],
            ["wharf", "wharves"],
            ["wheat", "wheat"],
            ["whiting", "whiting"],
            ["wife", "wives"],
            ["wildebeest", "wildebeest"],
            ["wish", "wishes"],
            ["woman", "women"],
            ["wood", "wood"],
            ["wool", "wool"],
            ["work", "works"],
            ["zombie", "zombies"],
            ["foo", "bar"]
        ];
    }

    /** @inheritdoc */
    protected function setUp(): void {
        $this->inflector = $this->getInflector();
    }

    /**
     * @dataProvider cases
     */
    public function testTableize(string $pascal, string $snake, string $camel) {
        $this->assertEquals($snake, $this->inflector->tableize($pascal));
    }

    /**
     * @dataProvider cases
     */
    public function testClassify(string $pascal, string $snake, string $camel) {
        $this->assertEquals($pascal, $this->inflector->classify($snake));
    }

    /**
     * @dataProvider cases
     */
    public function testCamelize(string $pascal, string $snake, string $camel) {
        $this->assertEquals($camel, $this->inflector->camelize($pascal));
        $this->assertEquals($camel, $this->inflector->camelize($snake));
    }

    /**
     * @dataProvider words
     * @group slow
     */
    public function testPluralize(string $singular, string $plural) {
        $this->assertEquals($plural, $this->inflector->pluralize($singular));
    }

    /**
     * @dataProvider words
     * @group slow
     */
    public function testSingularize(string $singular, string $plural) {
        $this->assertEquals($singular, $this->inflector->singularize($plural));
    }

}
