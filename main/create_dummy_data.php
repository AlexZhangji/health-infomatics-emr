<?php
/* Include our required headers */
require_once '../globals.php';
require_once "$srcdir/formdata.inc.php";
include_once("$srcdir/sql.inc");


function readCSV($csvFile){
	$file_handle = fopen($csvFile, 'r');
	while (!feof($file_handle) ) {
		$line_of_text[] = fgetcsv($file_handle, 1024);
	}
	fclose($file_handle);
	return $line_of_text;
}

createDummyPatient(30);
// importDiseaseDB();

function importDiseaseDB(){
  $tableName = "disease_data_gb";

  if ($result = sqlQuery("SHOW TABLES LIKE '$tableName'")) {
    if($result->num_rows == 1) {
        echo "Table exists";
    }
  }else {
    echo "create table";

    // create tables;
    sqlQuery("CREATE TABLE $tableName " .
    "(ensp_id VARCHAR(20), abrev VARCHAR(20), do_id INT, name VARCHAR(20));");

		// readin data from files
		$filePath = 'tsv_files/human_disease_knowledge_filtered.tsv';

		$fileData = readCSV($filePath);
		echo '<pre>';
		print_r($fileData);
		echo '</pre>';
  }
}


function getRandDate(){
  $start = strtotime("1972-10-01");
  $end =  strtotime("2015-12-31");

  $randomDate = date("Y-m-d", rand($start, $end));
  return $randomDate;
}

function createDummyPatient($numPpl){
  // templist
  $genderListDummy = array('male', 'female');
  // should use dictionary here
  $cityListDummy = array('los angeles','new york city', 'barcelona', 'seattle', 'chengdu');
  $stateListDummy = array('california', 'new york', 'catalonia', 'washington', 'sichuan');

  // comiles to something diablo like : speedy Crab, defiant lizard
  $nameAdjList = array("abandoned", "able", "absolute", "adorable", "adventurous", "academic", "acceptable", "acclaimed", "accomplished", "accurate", "aching", "acidic", "acrobatic", "babyish", "bad", "back", "baggy", "bare", "barren", "basic", "beautiful", "belated", "beloved",
  "calculating", "calm", "candid", "canine", "capital", "carefree", "careful", "careless", "caring", "damaged", "damp", "dangerous", "dapper", "daring", "darling", "dark", "dazzling", "dead", "deadly", "deafening", "dear", "dearest", "fast", "fat", "fatal",
  "fatherly", "favorable", "favorite", "fearful", "fearless", "feisty", "feline", "female", "feminine", "few", "radiant", "ragged", "rapid", "rare", "rash", "raw", "recent", "reckless", "rectangular");

  $nameAnimalList = array("Cat", "Flea", "Flowerpecker", "Fly", "Flying", "Fish", "Flying", "Frog", "Fossa", "Fox", "Frigatebird", "Frog", "Frogmouth", "Fulmar", "G", "Galago", "Gallinule", "Gannet", "Gar", "Garter", "Snake", "Gaur", "Gazelle",
   "Gecko", "Geoffroy", "s", "Cat", "Gerbil", "Gerenuk", "Giant", "Panda", "Giant", "Tortoise", "Gibbon", "Gila", "Monster", "Giraffe", "Gnu", "Goat", "Goatfish", "Goldfish", "Goose", "Gopher", "Goral", "Gorilla", "Gourami", "Grackle", "Grasshopper",
   "Greater", "Glider", "Grebe", "Green", "Iguana", "Grison", "Grizzly", "Bear", "Groundhog", "Grouse", "Guanaco", "Guinea", "Pig", "Gull", "Gundi", "H", "Hamster", "Harrier", "Hartebeest", "Hawaiian", "Honeycreeper", "Hawk", "Hedgehog", "Helmetshrike",
   "Hermit", "Crab", "Heron", "Himalayan", "Tahr", "Hippopotamus", "Hissing", "Cockroach", "Honeyeater", "Hornbill", "Hornet", "Horse", "Hoverfly", "Hummingbird", "Hutia", "Hyena", "Hyrax", "I", "Iberian", "Lynx", "Ibex", "Ibis", "Icterid", "Iguana",
   "Impala", "Insect", "J", "Jacana", "Jack", "Jackal", "Jaguar", "Jaguarundi", "Jay", "Jellyfish", "Jerboa", "Jungle", "Cat", "K", "Kangaroo", "Kangaroo", "Rat", "kerodon", "Kestrel", "King", "Cobra", "Kingbird", "Kingfisher", "Kinkajou", "Kite",
    "Kitten", "Kiwi", "Klipspringer", "Knifefish", "Koala", "Kodiak", "Bear", "Kodkod", "Koi", "Komodo", "Dragon", "Kookaburra", "Kowari", "Kudu", "Kultarr", "L", "Ladybug", "Lamb", "Lamprey", "Lapwing", "Leech", "Lemming", "Lemur", "Leopard",
     "Liger", "Lion", "Lionfish", "Lizard", "Llama", "Loach", "Lobster", "Long", "Tailed", "Tit", "Longspur", "Loon", "Loris", "Lory", "Lovebird", "Lynx", "Lyrebird", "M", "Macaw", "Mallard", "Mamba",
     "Mammoth", "Manakin", "Manatee", "Mandrill", "Manta", "Ray");


  // get a list of IDs from DB, so that no duplicate names will be created
  // $rawIdList = sqlQuery("SELECT id " .
  //   "FROM `patient_data_gb` ");
  //
  // $newId = end(array_values($rawIdList));

  for($i = 0; $i < $numPpl; $i++){
    $curDummy = [];

    $curName = $nameAdjList[array_rand($nameAdjList)] . ' ' . $nameAnimalList[array_rand($nameAnimalList)];

    //  array_rand can pass in second parameter to output more entires
    $curGender = $genderListDummy[array_rand($genderListDummy)];
    $curCity = $cityListDummy[array_rand($cityListDummy)];
    // Using city's index in cityList to get corresponding state name.
    // gosh.. why this looks so ugly...
    $cityIndex = array_search($curCity, $cityListDummy);
    $curState = array_values($stateListDummy)[$cityIndex];
    $curDate = getRandDate();

    $addNewPatient = sqlQuery("INSERT INTO patient_data_gb " .
      "(name,gender, DOB ,city_village,state_province) " .
      "VALUES('$curName', '$curGender','$curDate', '$curCity','$curState')");
  }

  print_r('created ' . $numPpl . ' patient.');

}


 ?>
