<?php

namespace ShmuelAKam\Greenjeans\Tests\Api;

class TestVars
{
    public const Test1 = new TestProfile('profile-34838408159', '6000000214522348827', 'Adam Test Profile .', "* [http://www.mechon-mamre.org/p/pt/pt0101.htm#26 Genesis 1:26-31]\r\n" . "* [http://www.mechon-mamre.org/p/pt/pt0102.htm#7 Genesis 2:7-25]\r\n" . "* [http://quran.");

    // com/2#2:29 Quran 2:29-38]. * [http://quran.com/3#3:59 Quran 3:59]. Wikipedia: * [http://en.wikipedia.org/wiki/Adam Adam] & [http://he.wikipedia.org/wiki/%D7%90%D7%93%D7%9D_%D7%95%D7%97%D7%95%D7%94 אדם וחוה] * [http://en.wikipedia.org/wiki/Generations_of_Adam Generations of Adam] * [http://en.wikipedia.org/wiki/Adam_in_Islam Adam in Islam] The name 'Adam' originated from the Hebrew word, pronounced aw-dawm, which means man, as a species, male and female. The Holy Scriptures use this literal meaning for Adam, meaning simply humans. '
    public const Test2 = new TestProfile('profile-34838408177', '6000000214522520831', 'Seth Test Profile .', "* [http://www.mechon-mamre.org/p/pt/pt0104.htm#25 Genesis 4:25-26]\r\n" . "* [http://www.mechon-mamre.org/p/pt/pt0105.htm#3 Genesis 5:3-8] lists his place in");

    // the ten generations between Adam & Noah. Normally genealogies are traced by the first-born sons, but Cain killed Abel and then was banished, so Seth became almost like a new “first-born”. * [http://www.biblestudytools.com/history/flavius-josephus/antiquities-jews/book-1/chapter-2.html Antiquities of the Jews, Book 1 Chapter 2:3.] Wikipedia: * [http://en.wikipedia.org/wiki/Seth Seth] and [http://he.wikipedia.org/wiki/%D7%A9%D7%AA שת]. * [https://en.wikipedia.org/wiki/Seth#Islam Seth in Islam.] Seth, 3rd son of Adam & Eve, was born after Cain murdered Abel. So the family line is continued through him. His name denoted this being derived from the root meaning 'appointed-given' instead of his brother(s), as Eve herself explains. ---- See also: [http://www.geni.com/profile/index/6000000003645707049 Seth Sat-naal]
    public const Test3 = new TestProfile('profile-34838408195', '6000000214522461839', 'Enosh Test Profile .', "* [http://www.mechon-mamre.org/p/pt/pt0104.htm#26 Genesis 4:26.]\r\n" . "* [http://www.mechon-mamre.org/p/pt/pt0105.htm#6 Genesis 5:6-11.]\r\n" . "* [http://www.pse");

    // udoepigrapha.com/jubilees/4.htm Book of Jubilees: 4:11-13.] Wikipedia: [http://en.wikipedia.org/wiki/Enos_%28Bible%29 Enos] See also (don't merge): [http://www.geni.com/profile/index/6000000003645882249 Henokh] He married his sister, No'am (this necessary practice was discontinued after his generation, once there were more distant relations available to marry), Ethiopian Orthodox tradition considers him a 'faithful and righteous servant of God', and further credits him with the introduction, following a divine revelation, of the Ge'ez alphabet in its original, consonant-only form, 'as an instrument for codifying the laws'. He is commemorated as one of the Holy Forefathers in the Calendar of Saints of the Armenian Apostolic Church on July 30. According to the Book of Jubilees (4:11-13) in the Ethiopian Orthodox Bible, Enos was born in the Year of the World 235, and 'he began to call on the name of the Lord on the earth.' He married his sister, No'am (this necessary practice was discontinued after his generation, once there were more distant relations available to marry), and she bare him Kenan in the year 325. Ethiopian Orthodox tradition considers him a 'faithful and righteous servant of God', and further credits him with the introduction, following a divine revelation, of the Ge'ez alphabet in its original, consonant-only form, 'as an instrument for codifying the laws'[1]. Enos in Hinduism is mentioned as Anoohan and is considered being the son of Seth (Shwetha-nama)[citation needed]. The text carried about Enoch was mentioned in the last Purana the Bhavishya Purana which was summarised text done in Sanskrit and attributed to Rishi Vyasa, the compiler of the Vedas. '
    public const Test_Profiles
        = [
            "Adam"  => self::Test1,
            "Seth"  => self::Test2,
            "Enosh" => self::Test3
        ];

}

// =============================================================================
// Data structure for data from Profile tests.
class TestProfile
{
    public string $GUID;
    public string $ID;
    public string $Name;
    public string $AboutMe;

    public function __construct(string $ID, string $GUID, string $Name, string $AboutMe)
    {
        $this->ID      = $ID;
        $this->GUID    = $GUID;
        $this->Name    = $Name;
        $this->AboutMe = $AboutMe;
    }
}
