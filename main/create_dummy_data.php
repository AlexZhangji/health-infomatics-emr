<?php

/* Include our required headers */
require_once '../globals.php';
require_once "$srcdir/formdata.inc.php";
include_once "$srcdir/sql.inc";

function readCSV($csvFile)
{
    $file_handle = fopen($csvFile, 'r');
    while (!feof($file_handle)) {
        $line_of_text[] = fgetcsv($file_handle, 1000, "\t");
    }
    fclose($file_handle);

    return $line_of_text;
}

function readTxt($filePath)
{
    $file_handle = fopen($filePath, 'r');
    while (!feof($file_handle)) {
        $line_of_text[] = fgets($file_handle, 4096);
        // $parts = explode('=', $line_of_text);
    }
    fclose($file_handle);

    return $line_of_text;
}

// $textDB = readTxt('tsv_files/icd10cm_order_2016.txt');
//
// foreach ($textDB as $text) {
//     $text = preg_split('/\s+/', $text);
//     print_r($text);
// }

// createDummyPatient(30);
// importDiseaseDB();
// createDummyVisit();

function createDummyVisit()
{
    $tableName = 'patient_visit_gb';

    if ($result = sqlQuery("SHOW TABLES LIKE '$tableName'")) {
        echo 'Table exists';
    } else {
        echo 'create dummy visit table';

        // create visit database
        //  Difference between VARCHAR, TEXT, TINYTEXT and etc.
        //  http://stackoverflow.com/questions/7755629/varchar255-vs-tinytext-tinyblob-and-varchar65535-vs-blob-text
        sqlQuery("CREATE TABLE $tableName ".
        '(visit_id BIGINT NOT NULL AUTO_INCREMENT, PRIMARY KEY (visit_id),'.
        ' p_id BIGINT, date date, weight INT, height INT, bph INT,'.
        ' bpi INT, temperature INT, pulse INT, respiratory_rate INT,bos INT, note VARCHAR(255) ,'.
        ' diagnosis VARCHAR(255), CC VARCHAR(255), symptoms VARCHAR(255), Rx VARCHAR(255) '.
        ');');

        // get the list of patient Ids.
        $idList = array();
        $patientIdList = mysql_query('SELECT id FROM patient_data_gb');
        while ($row = mysql_fetch_array($patientIdList)) {
            $idList[] = $row['id'];
        }

        foreach ($idList as $_id) {
            $numVisit = rand(0, 3);

            for ($i = 0; $i < $numVisit; ++$i) {
                // generate dummy data

                $_date = getRandDate(strtotime('2012-10-01'), strtotime('2016-10-31'));
                $_weight = rand(40, 80);
                $_height = rand(150, 190);
                $_bpl = rand(60, 85);
                $_bph = rand(100, 125);
                $_temp = rand(26, 30);
                $_pulse = rand(200, 220);
                $_resRate = rand(70, 90);

                sqlQuery('INSERT INTO patient_visit_gb '.
            '(p_id, date ,weight,height, bph,bpi, temperature, pulse, respiratory_rate ) '.
            "VALUES('$_id', '$_date','$_weight', '$_height','$_bph','$_bpl','$_temp', '$_pulse', '$_resRate')");
            }
        }
    }
}

function importDiseaseDB()
{
    $tableName = 'disease_data_gb';

    if ($result = sqlQuery("SHOW TABLES LIKE '$tableName'")) {
        echo 'Table exists';
    } else {
        echo 'create table';
        // create tables;
        sqlQuery("CREATE TABLE $tableName ".
        '(ensp_id VARCHAR(20), abrev VARCHAR(20), do_id BIGINT, name VARCHAR(255) , sub_name VARCHAR(20), curated_level INT);');

        // readin data from files
        $filePath = 'tsv_files/human_disease_knowledge_filtered.tsv';
        $fileData = readCSV($filePath);

        foreach ($fileData as $diseaseData) {
            $enspNum = mysql_real_escape_string(intval(substr($diseaseData[0], 4)));
            $abrev = mysql_real_escape_string($diseaseData[1]);
            $doId = mysql_real_escape_string(intval(substr($diseaseData[2], 5)));
            $diseaseName = mysql_real_escape_string($diseaseData[3]);
            $subName = mysql_real_escape_string($diseaseData[4]);
            $curatedLevel = mysql_real_escape_string(intval($diseaseData[6]));

            sqlQuery('INSERT INTO disease_data_gb '.
          '(ensp_id,abrev, do_id ,name,sub_name, curated_level) '.
          "VALUES('$enspNum', '$abrev','$doId', '$diseaseName','$subName','$curatedLevel')");
        }
    }
}

function getRandDate($start, $end)
{
    $randomDate = date('Y-m-d', rand($start, $end));

    return $randomDate;
}

function createDummyPatient($numPpl)
{
    $tableName = 'patient_data_gb';

    if ($result = sqlQuery("SHOW TABLES LIKE '$tableName'")) {
        echo 'Table exists';
    } else {

      echo 'create patient table';
      // create tables;
      sqlQuery("CREATE TABLE $tableName ".
      '(id BIGINT NOT NULL AUTO_INCREMENT, PRIMARY KEY (id),'.
      'name VARCHAR(255), gender VARCHAR(255), DOB date, city_village VARCHAR(255),'.
      'state_province VARCHAR(255), address_1 VARCHAR(255), address_2 VARCHAR(255), postal_num BIGINT, phone_num BIGINT;');

        // templist
      $genderListDummy = array('male', 'female');
      // should use dictionary here
      $cityListDummy = array('los angeles', 'new york city', 'barcelona', 'seattle', 'chengdu');
      $stateListDummy = array('california', 'new york', 'catalonia', 'washington', 'sichuan');

      // comiles to something diablo like : speedy Crab, defiant lizard
      $nameAdjList = array('abandoned', 'able', 'absolute', 'adorable', 'adventurous', 'academic', 'acceptable', 'acclaimed', 'accomplished', 'accurate', 'aching', 'acidic', 'acrobatic', 'babyish', 'bad', 'back', 'baggy', 'bare', 'barren', 'basic', 'beautiful', 'belated', 'beloved',
      'calculating', 'calm', 'candid', 'canine', 'capital', 'carefree', 'careful', 'careless', 'caring', 'damaged', 'damp', 'dangerous', 'dapper', 'daring', 'darling', 'dark', 'dazzling', 'dead', 'deadly', 'deafening', 'dear', 'dearest', 'fast', 'fat', 'fatal',
      'fatherly', 'favorable', 'favorite', 'fearful', 'fearless', 'feisty', 'feline', 'female', 'feminine', 'few', 'radiant', 'ragged', 'rapid', 'rare', 'rash', 'raw', 'recent', 'reckless', 'rectangular', );

            $nameAnimalList = array('Cat', 'Flea', 'Flowerpecker', 'Fly', 'Flying', 'Fish', 'Flying', 'Frog', 'Fossa', 'Fox', 'Frigatebird', 'Frog', 'Frogmouth', 'Fulmar', 'G', 'Galago', 'Gallinule', 'Gannet', 'Gar', 'Garter', 'Snake', 'Gaur', 'Gazelle',
       'Gecko', 'Geoffroy', 's', 'Cat', 'Gerbil', 'Gerenuk', 'Giant', 'Panda', 'Giant', 'Tortoise', 'Gibbon', 'Gila', 'Monster', 'Giraffe', 'Gnu', 'Goat', 'Goatfish', 'Goldfish', 'Goose', 'Gopher', 'Goral', 'Gorilla', 'Gourami', 'Grackle', 'Grasshopper',
       'Greater', 'Glider', 'Grebe', 'Green', 'Iguana', 'Grison', 'Grizzly', 'Bear', 'Groundhog', 'Grouse', 'Guanaco', 'Guinea', 'Pig', 'Gull', 'Gundi', 'H', 'Hamster', 'Harrier', 'Hartebeest', 'Hawaiian', 'Honeycreeper', 'Hawk', 'Hedgehog', 'Helmetshrike',
       'Hermit', 'Crab', 'Heron', 'Himalayan', 'Tahr', 'Hippopotamus', 'Hissing', 'Cockroach', 'Honeyeater', 'Hornbill', 'Hornet', 'Horse', 'Hoverfly', 'Hummingbird', 'Hutia', 'Hyena', 'Hyrax', 'I', 'Iberian', 'Lynx', 'Ibex', 'Ibis', 'Icterid', 'Iguana',
       'Impala', 'Insect', 'J', 'Jacana', 'Jack', 'Jackal', 'Jaguar', 'Jaguarundi', 'Jay', 'Jellyfish', 'Jerboa', 'Jungle', 'Cat', 'K', 'Kangaroo', 'Kangaroo', 'Rat', 'kerodon', 'Kestrel', 'King', 'Cobra', 'Kingbird', 'Kingfisher', 'Kinkajou', 'Kite',
        'Kitten', 'Kiwi', 'Klipspringer', 'Knifefish', 'Koala', 'Kodiak', 'Bear', 'Kodkod', 'Koi', 'Komodo', 'Dragon', 'Kookaburra', 'Kowari', 'Kudu', 'Kultarr', 'L', 'Ladybug', 'Lamb', 'Lamprey', 'Lapwing', 'Leech', 'Lemming', 'Lemur', 'Leopard',
         'Liger', 'Lion', 'Lionfish', 'Lizard', 'Llama', 'Loach', 'Lobster', 'Long', 'Tailed', 'Tit', 'Longspur', 'Loon', 'Loris', 'Lory', 'Lovebird', 'Lynx', 'Lyrebird', 'M', 'Macaw', 'Mallard', 'Mamba',
         'Mammoth', 'Manakin', 'Manatee', 'Mandrill', 'Manta', 'Ray', );

      // get a list of IDs from DB, so that no duplicate names will be created
      // $rawIdList = sqlQuery("SELECT id " .
      //   "FROM `patient_data_gb` ");

      // $newId = end(array_values($rawIdList));

      for ($i = 0; $i < $numPpl; ++$i) {
          $curDummy = [];

          $curName = $nameAdjList[array_rand($nameAdjList)].' '.$nameAnimalList[array_rand($nameAnimalList)];

          //  array_rand can pass in second parameter to output more entires

          $curGender = $genderListDummy[array_rand($genderListDummy)];
          $curCity = $cityListDummy[array_rand($cityListDummy)];

          // Using city's index in cityList to get corresponding state name.
          // gosh.. why this looks so ugly...

          $cityIndex = array_search($curCity, $cityListDummy);
          $curState = array_values($stateListDummy)[$cityIndex];
          $curDate = getRandDate(strtotime('1972-10-01'), strtotime('2015-12-31'));

          $addNewPatient = sqlQuery('INSERT INTO patient_data_gb '.
          '(name,gender, DOB ,city_village,state_province) '.
          "VALUES('$curName', '$curGender','$curDate', '$curCity','$curState')");
      }
    }

    print_r('created '.$numPpl.' patient.');
}
