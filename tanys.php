<?php
date_default_timezone_set('Asia/Jakarta');
include 'LineCross.php';
use x9119x\LineCross;

const AUTH_TOKEN = ""; //Masukin Token Mu
const ADMIN_MID  = "udf060a89ebb2af83af77edddb767c329"; //Masukan MID admin


$AuthInfo = new \x9119x\AuthInfo(AUTH_TOKEN);

try{
    $Line = new LineCross(); //Ini yang buat login qr
//	$Line = new LineCross($AuthInfo); //Ini buat yang login token
}catch(x9119x\TalkException $e) {
    echo "\033[0;31m[ERROR]\033[0m".$e->reason . PHP_EOL;
    exit;
}
print_r($Line->LineService->getProfile());
$Pooling = new Pooling($Line, $AuthInfo);
while(true){
    $Ops = $Pooling->Fetch();

    if(empty($Ops))
        continue;
    foreach ($Pooling->Alloc($Ops) as $msg) {
        print_r($msg);
    }
}

class Pooling{
    public $Line;
    public $OpType;
    public $ContactStatus;
    public $ContentType;
    private $mid;
    public $_from;

    public function __construct($Line, $AuthInfo) {
        $this->Line = $Line;
        $this->OpType = new x9119x\OpType();
        $this->ContactStatus = new x9119x\ContactStatus();
        $this->ContentType = new x9119x\ContentType();

    }
    public function Fetch(){
        try {
            $Ops = $this->Line->PollService->start(100);
        } catch (x9119x\TalkException $e) {
            echo "\033[0;31m[ERROR]\033[0m".$e->reason . PHP_EOL;
            exit;
        }catch(Thrift\Exception\TTransportException $e){
            echo $e->getMessage().PHP_EOL;
        }
        $msg = '';
        if(empty($Ops)){
            return;
        }
        return $Ops;
    }

    public function Alloc($Ops){

        foreach ($Ops as $Op) {
            $this->Line->AuthInfo->Rev = max(intval($Op->revision), intval($this->Line->AuthInfo->Rev));
            switch ($Op->type) {
                case $this->OpType::RECEIVE_MESSAGE:
                    $msg = $Op->message;
                    $this->_from = $Op->message->_from;

                    $text = $Op->message->text;

                    $args = explode(" ", $text);
                    if($args[0] == "member"){
                        if($this->_from != ADMIN_MID){
                            $this->Line->LineService->sendMessage("Lah siapa lu ngntd!", $Op->message->to);
                            return;
                        }

                        //GET GROUP INFO
                        $getGroupInfo = $this->Line->LineService->getGroup($Op->message->to);

                        $member = "";
                        foreach($getGroupInfo->members as $key){
                            $members .= "@" . $key->displayName . " ";

                        }
                        $this->Line->LineService->sendMessage($members, $Op->message->to);
		    }else if ($args[0] == "Speed"){
			if($this->_from != ADMIN_MID){
                            $this->Line->LineService->sendMessage("Lah siapa lu ngntd!", $Op->message->to);
                            return;
                        }
			//Get TIME
			$startTime = microtime(true);
			$this->Line->LineService->sendMessage("Processing..", $Op->message->to);
			$this->Line->LineService->sendMessage("Taken progress:  " . number_format(( microtime(true) - $startTime), 4) . " Seconds\n", $Op->message->to);
                    }else if($args[0] == "Ratakan"){
                        if($this->_from != ADMIN_MID){
                            $this->Line->LineService->sendMessage("Lah siapa lu ngntd!", $Op->message->to);
                            return;
                        }

                        //GET GROUP INFO
                        $getGroupInfo = $this->Line->LineService->getGroup($Op->message->to);

                        //GET ALL THE MEMBERS IN THE GROUP
                        $memIDs = array();
                        $count = 0;
                        foreach($getGroupInfo->members as $key){
                            $memIDs["mid"][] = $key->mid;
                            $count++;
                        }

                        //COUNT AMOUNT OF MEMBERS IN THE GROUP
                        if($count > 50){
                            echo "Anggota lebih dari 50\n";
                        }else{
                            if(($nMid = array_search($Op->message->_from, $memIDs["mid"])) !== false){
                                unset($memIDs["mid"][$nMid]);
                            }
                            try{
                                foreach($memIDs["mid"] as $key){
                                    $this->Line->LineService->kickoutFromGroup($Op->message->to, (array) $key);
                                }
                            }catch(\x9119x\TalkException $ex){
                                echo $ex->getMessage() . "\n";
                            }
                        }
                    }

                    switch($Op->message->contentType){
                        case $this->ContentType::CONTACT:
                            $this->mid = $Op->message->contentMetadata['mid'];
                            try{
                                $content = $this->Line->LineService->getContact($this->mid);
                                $this->Line->LineService->sendMessage($this->MessageContactBuilder((array) $content),
                                    $Op->message->_from);
                            }catch(x9119x\TalkException $ex){
                                echo $ex . PHP_EOL;
                            }
                            break;

                        default:
                            break;
                    }

                    yield $msg;
                    break;

                case $this->OpType::NOTIFIED_INVITE_INTO_GROUP:
                    //GET GROUP ID
                    $getGroupIDByInvite = $this->Line->LineService->getGroupIdsInvited();

                    //ACCEPT GROUP INVITATION
                    try{
                        var_dump($this->Line->LineService->acceptGroupInvitation($getGroupIDByInvite[0]));
                    }catch(\x9119x\TalkException $ex){
                        echo $ex->getMessage().PHP_EOL;
                    }

                    //GET GROUP COMPACT
                    $getCompactGroup    = $this->Line->LineService->getCompactGroup($getGroupIDByInvite[0]);

                    yield $getCompactGroup;
                    break;
                default:
                    break;
            }
        }
    }

    public function MessageContactBuilder(array $content){
        $message = "";

        $mid                    = $content["mid"];
        $createdTime            = $content["createdTime"];
        $type                   = $content["type"];
        $status                 = $content["status"];
        $relation               = $content["relation"];
        $displayName            = $content["displayName"];
        $phoneticName           = $content["phoneticName"];
        $pictureStatus          = $content["pictureStatus"];
        $thumbnailUrl           = $content["thumbnailUrl"];
        $statusMessage          = $content["statusMessage"];
        $displayNameOverridden  = $content["displayNameOverridden"];
        $favoriteTime           = $content["favoriteTime"];
        $capableVoiceCall       = $content["capableVoiceCall"];
        $capableVideoCall       = $content["capableVideoCall"];
        $capableMyhome          = $content["capableMyhome"];
        $capableBuddy           = $content["capableBuddy"];
        $attributes             = $content["attributes"];
        $settings               = $content["settings"];
        $picturePath            = $content["picturePath"];

        if (isset($mid)) {
            $message = "UserID : " . $mid . PHP_EOL;
        }
        if (isset($createdTime) && $createdTime != 0) {
            $message .= "Created Time : " . $createdTime . PHP_EOL;
        }
        if (isset($type) && $type != "NULL") {
            $message .= "Account Type : " . $type . PHP_EOL;
        }
        if (isset($status) && $status != 0) {
            $message .= "Status : " . $status . PHP_EOL;
        }
        if (isset($relation)) {
            $message .= "Relation : " . $relation . PHP_EOL;
        }
        if (isset($displayName)) {
            $message .= "Display Name : " . $displayName . PHP_EOL;
        }
        if (isset($phoneticName) && $phoneticName != "NULL") {
            $message .= "Phonetic Name : " . $phoneticName . PHP_EOL;
        }
        if (isset($pictureStatus)) {
            $message .= "Picture URL : http://dl.profile.line-cdn.net/" . $pictureStatus . PHP_EOL;
        }
        if (isset($thumbnailUrl) && $thumbnailUrl != "NULL") {
            $message .= "Thumbnail URL : " . $thumbnailUrl . PHP_EOL;
        }
        if (isset($statusMessage)) {
            $message .= "Status Message : " . $statusMessage . PHP_EOL;
        }
        if (isset($displayNameOverridden) && $displayNameOverridden != "NULL") {
            $message .= "Display Name Override : " . $displayNameOverridden . PHP_EOL;
        }
        if (isset($favoriteTime) && $favoriteTime != 0) {
            $message .= "Favorite Time : " . $favoriteTime . PHP_EOL;
        }
        if (isset($capableVoiceCall) && $capableVoiceCall != false) {
            $message .= "Capable VoiceCall : " . $capableVoiceCall . PHP_EOL;
        }
        if (isset($capableVideoCall) && $capableVideoCall != false) {
            $message .= "Capable VideoCall : " . $capableVideoCall . PHP_EOL;
        }
        if (isset($capableMyhome) && $capableMyhome != false) {
            $message .= "Capable Myhome : " . $capableMyhome . PHP_EOL;
        }
        if (isset($capableBuddy) && $capableBuddy != false) {
            $message .= "Capable Buddy  : " . $capableBuddy . PHP_EOL;
        }
        if (isset($attributes)) {
            $message .= "Attributes : " . $attributes . PHP_EOL;
        }
        if (isset($settings) && $settings != 0) {
            $message .= "Settings : " . $settings . PHP_EOL;
        }
        if (isset($picturePath)) {
            $message .= "Picture Path : " . $picturePath . PHP_EOL;
        }

        return $message;
    }
}
