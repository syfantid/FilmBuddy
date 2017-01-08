<?php

/**
 * Created by IntelliJ IDEA.
 * User: sifantid
 * Date: 22/12/2016
 * Time: 8:08 μμ
 * Handles the Facebook login server-side and background processing of Facebook data
 */
if(!session_id()) {
    session_start();
}
set_include_path($_SERVER["DOCUMENT_ROOT"] . "/film_buddy/src/includes/");
require_once('vendor/autoload.php');
require_once('constants.php');

function Redirect($url, $permanent = false)
{
    if (headers_sent() === false)
    {
        header('Location: ' . $url, true, ($permanent === true) ? 301 : 302);
    }

    exit();
}

$fb = new Facebook\Facebook([
    'app_id' => FBID, // Replace {app-id} with your app id
    'app_secret' => FBSECRET,
    'default_graph_version' => 'v2.2',
]);
$helper = $fb->getJavaScriptHelper();

try {
    $accessToken = $helper->getAccessToken();
} catch(Facebook\Exceptions\FacebookResponseException $e) {
    // When Graph returns an error
    /*echo 'Graph returned an error: ' . $e->getMessage();
    exit;*/
    Redirect("./404.html");
} catch(Facebook\Exceptions\FacebookSDKException $e) {
    // When validation fails or other local issues
   /* echo 'Facebook SDK returned an error: ' . $e->getMessage();
    exit;*/
    Redirect("./404.html");
}

if (! isset($accessToken)) {
    /*echo 'No cookie set or no OAuth data could be obtained from cookie.';
    exit;*/
    Redirect("./404.html");
}

// Logged in
/*echo '<h3>Access Token</h3>';
var_dump($accessToken->getValue());*/

$_SESSION['fb_access_token'] = (string) $accessToken;
$since = 2012; // todo Pass the $since parameter


function get_data($fb)
{
    try {
        // Returns a `Facebook\FacebookResponse` object
        $response = $fb->get('/me?fields=id,name,email,likes.limit(500){name,category},posts.limit(500){message}', $_SESSION['fb_access_token']);
    } catch (Facebook\Exceptions\FacebookResponseException $e) {
        /*echo 'Graph returned an error: ' . $e->getMessage();
        exit;*/
        Redirect("./404.html");
    } catch (Facebook\Exceptions\FacebookSDKException $e) {
        /*echo 'Facebook SDK returned an error: ' . $e->getMessage();
        exit;*/
        Redirect("./404.html");
    }

    $user = $response->getGraphUser()->asArray();
    return $user;
}

function get_user($fb)
{
    try {
        // Returns a `Facebook\FacebookResponse` object
        $response = $fb->get('/me?fields=id,name,email', $_SESSION['fb_access_token']);
    } catch (Facebook\Exceptions\FacebookResponseException $e) {
        /*echo 'Graph returned an error: ' . $e->getMessage();
        exit;*/
        Redirect("./404.html");
    } catch (Facebook\Exceptions\FacebookSDKException $e) {
        /*echo 'Facebook SDK returned an error: ' . $e->getMessage();
        exit;*/
        Redirect("./404.html");
    }

    $user = $response->getGraphUser()->asArray();
    return $user;
}

function get_likes($fb, $since)
{
    try {
        $getPages = $fb->get('/me/likes?fields=name,category&since=' . $since, $_SESSION['fb_access_token']);
    } catch (Exception $e) {
        Redirect("./404.html");
    }
    $likes = $getPages->getGraphEdge();

    $totalLikes = array();

    if ($fb->next($likes)) {
        $likesArray = $likes->asArray();
        $totalLikes = array_merge($totalLikes, $likesArray);
        while ($likes = $fb->next($likes)) {
            $likesArray = $likes->asArray();
            $totalLikes = array_merge($totalLikes, $likesArray);
        }
    } else {
        $likesArray = $likes->asArray();
        $totalLikes = array_merge($totalLikes, $likesArray);
    }

    return $totalLikes;
}

function get_posts($fb, $since)
{
    try {
        $getPosts = $fb->get('/me/posts?fields=message&since=' . $since, $_SESSION['fb_access_token']);
    } catch (Exception $e) {
        Redirect("./404.html");
    }
    $posts = $getPosts->getGraphEdge();

    $totalPosts = array();

    if ($fb->next($posts)) {
        $postsArray = $posts->asArray();
        $totalPosts = array_merge($totalPosts, $postsArray);
        while ($posts = $fb->next($posts)) {
            $postsArray = $posts->asArray();
            $totalPosts = array_merge($totalPosts, $postsArray);
        }
    } else {
        $postsArray = $posts->asArray();
        $totalPosts = array_merge($totalPosts, $postsArray);
    }

    return $totalPosts;
}

/* ***************************************************************** */
class processing
{
    protected $semantic_words = array(); // For caching semantic relations

    /* Clear input */
    function clear_text($input_text)
    {
        $input_text = preg_replace("/\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|$!:,.;]*[A-Z0-9+&@#\/%=~_|$]/i",
            " ", $input_text); // Remove https URLs
        $input_text = preg_replace("/www\.[a-z\.0-9]+\/?[[a-z\.0-9?=]+]?/i", " ", $input_text); // Remove plain www URLs
        $input_text = preg_replace('/[^a-zA-Z ]/', ' ', $input_text); // Remove non English latin characters
        $input_text = strtolower($input_text); // Convert to lower case
        $input_text = preg_replace('/\b[a-z]\b/', ' ', $input_text); // Remove single characters
        $input_text = preg_replace('/\s+/', ' ', $input_text); // Remove multiple whitespaces

        return $input_text;
    }

    /* Function taken from https://gist.github.com/keithmorris/4155220 */
    function removeCommonWords($input){
        // EEEEEEK Stop words
        $commonWords = array('a','able','about','above','abroad','according','accordingly','across','actually','adj','after','afterwards','again','against','ago','ahead','ain\'t','all','allow','allows','almost','alone','along','alongside','already','also','although','always','am','amid','amidst','among','amongst','an','and','another','any','anybody','anyhow','anyone','anything','anyway','anyways','anywhere','apart','appear','appreciate','appropriate','are','aren\'t','around','as','a\'s','aside','ask','asking','associated','at','available','away','awfully','b','back','backward','backwards','be','became','because','become','becomes','becoming','been','before','beforehand','begin','behind','being','believe','below','beside','besides','best','better','between','beyond','both','brief','but','by','c','came','can','cannot','cant','can\'t','caption','cause','causes','certain','certainly','changes','clearly','c\'mon','co','co.','com','come','comes','concerning','consequently','consider','considering','contain','containing','contains','corresponding','could','couldn\'t','course','c\'s','currently','d','dare','daren\'t','definitely','described','despite','did','didn\'t','different','directly','do','does','doesn\'t','doing','done','don\'t','down','downwards','during','e','each','edu','eg','eight','eighty','either','else','elsewhere','end','ending','enough','entirely','especially','et','etc','even','ever','evermore','every','everybody','everyone','everything','everywhere','ex','exactly','example','except','f','fairly','far','farther','few','fewer','fifth','first','five','followed','following','follows','for','forever','former','formerly','forth','forward','found','four','from','further','furthermore','g','get','gets','getting','given','gives','go','goes','going','gone','got','gotten','greetings','h','had','hadn\'t','half','happens','hardly','has','hasn\'t','have','haven\'t','having','he','he\'d','he\'ll','hello','help','hence','her','here','hereafter','hereby','herein','here\'s','hereupon','hers','herself','he\'s','hi','him','himself','his','hither','hopefully','how','howbeit','however','hundred','i','i\'d','ie','if','ignored','i\'ll','i\'m','immediate','in','inasmuch','inc','inc.','indeed','indicate','indicated','indicates','inner','inside','insofar','instead','into','inward','is','isn\'t','it','it\'d','it\'ll','its','it\'s','itself','i\'ve','j','just','k','keep','keeps','kept','know','known','knows','l','last','lately','later','latter','latterly','least','less','lest','let','let\'s','like','liked','likely','likewise','little','look','looking','looks','low','lower','ltd','m','made','mainly','make','makes','many','may','maybe','mayn\'t','me','mean','meantime','meanwhile','merely','might','mightn\'t','mine','minus','miss','more','moreover','most','mostly','mr','mrs','much','must','mustn\'t','my','myself','n','name','namely','nd','near','nearly','necessary','need','needn\'t','needs','neither','never','neverf','neverless','nevertheless','new','next','nine','ninety','no','nobody','non','none','nonetheless','noone','no-one','nor','normally','not','nothing','notwithstanding','novel','now','nowhere','o','obviously','of','off','often','oh','ok','okay','old','on','once','one','ones','one\'s','only','onto','opposite','or','other','others','otherwise','ought','oughtn\'t','our','ours','ourselves','out','outside','over','overall','own','p','particular','particularly','past','per','perhaps','placed','please','plus','possible','presumably','probably','provided','provides','q','que','quite','qv','r','rather','rd','re','really','reasonably','recent','recently','regarding','regardless','regards','relatively','respectively','right','round','s','said','same','saw','say','saying','says','second','secondly','see','seeing','seem','seemed','seeming','seems','seen','self','selves','sensible','sent','serious','seriously','seven','several','shall','shan\'t','she','she\'d','she\'ll','she\'s','should','shouldn\'t','since','six','so','some','somebody','someday','somehow','someone','something','sometime','sometimes','somewhat','somewhere','soon','sorry','specified','specify','specifying','still','sub','such','sup','sure','t','take','taken','taking','tell','tends','th','than','thank','thanks','thanx','that','that\'ll','thats','that\'s','that\'ve','the','their','theirs','them','themselves','then','thence','there','thereafter','thereby','there\'d','therefore','therein','there\'ll','there\'re','theres','there\'s','thereupon','there\'ve','these','they','they\'d','they\'ll','they\'re','they\'ve','thing','things','think','third','thirty','this','thorough','thoroughly','those','though','three','through','throughout','thru','thus','till','to','together','too','took','toward','towards','tried','tries','truly','try','trying','t\'s','twice','two','u','un','under','underneath','undoing','unfortunately','unless','unlike','unlikely','until','unto','up','upon','upwards','us','use','used','useful','uses','using','usually','v','value','various','versus','very','via','viz','vs','w','want','wants','was','wasn\'t','way','we','we\'d','welcome','well','we\'ll','went','were','we\'re','weren\'t','we\'ve','what','whatever','what\'ll','what\'s','what\'ve','when','whence','whenever','where','whereafter','whereas','whereby','wherein','where\'s','whereupon','wherever','whether','which','whichever','while','whilst','whither','who','who\'d','whoever','whole','who\'ll','whom','whomever','who\'s','whose','why','will','willing','wish','with','within','without','wonder','won\'t','would','wouldn\'t','x','y','yes','yet','you','you\'d','you\'ll','your','you\'re','yours','yourself','yourselves','you\'ve','z','zero');

        return preg_replace('/\b('.implode('|',$commonWords).')\b/','',$input);
    }

    /* Semantic Expansion of input */
    public function process_text($input_text)
    {
        $input_text = $this->clear_text($input_text); // Clear input text line
        $input_text = $this->removeCommonWords($input_text); // Remove stop words
        $words = array_count_values(str_word_count($input_text, 1)); // Distinct words with frequencies
        arsort($words); // Sort array based on most frequent words

        $outCount = 0;
        $synonyms = 3;
        $final_text = "";
        foreach ($words as $word => $frequency) {
            $final_text = $final_text . $word . " "; // Add the word itself
            if (!array_key_exists($word, $this->semantic_words)) {
                $json = file_get_contents('http://api.datamuse.com/words?rd=' . $word); // Get the JSON response
                $array = json_decode($json, true); // Convert JSON to associative array
                $related_words = "";
                $count = 0;
                foreach ($array as $related_word) {
                    $related_words = $related_words . " " . $related_word['word']; // All the related words
                    $count++;
                    if($count == $synonyms) { // Get a specific number of synonyms
                        break;
                    }
                }
                $this->semantic_words[$word] = $related_words; // Cache the semantic relationship
            }
            $final_text = $final_text . $this->semantic_words[$word] . " "; // Add the word's semantically related words
            $outCount++;
            if($outCount == 60) { // Get the 50 most frequent words
                break;
            }
        }
        return $final_text;
    }
}

$data = get_data($fb); // The user's data
$likesArray = $data['likes']; // The user's likes
$postsArray = $data['posts']; // The user's posts
$postsString = ""; // User's post messages as a string
foreach ($postsArray as $post) {
    if(array_key_exists('message', $post)) {
        $postsString = $postsString . $post['message'] . " ";
    }
}
$aux = new processing();
$postsString = $aux->process_text($postsString); // The final processed posts' texts
$postsString = preg_replace("/ /",",",$postsString);

/*print_r("cat");*/
/*print_r(implode(' ', array_slice(explode(' ', $postsString), 0, 10)));*/
print_r($postsString);
/*print_r("gr,calomel,rand,10-30,youthfully,boyishly,fashionably,girlishly,youth,young,youthfulness,juvenility,exchange,interchange,convert,switch,erasmus,desiderius,erasmus,geert,geerts,gerhard,gerhards,workshop,shop,seminar,symposium,people,masses,citizenry,live,live,reside,inhabit,go,training,preparation,grooming,education,youthfullyyours,greece,ellas,portugal,spain,dj,disc,jockey,disk,jockey,diskjockey,pm,necropsy,autopsy,postmortem,examination,thessaloniki,thessalonika,salonica,thessalonica,spotted,patched,patterned,flyblown,awesome,amazing,awful,impressive,diy,repair,repairing,repairs,youthfullyyoursgr,gili,said,asked,see,wine,vino,wine-colored,winery,greek,hellene,grecian,hellenic,love,passion,enjoy,beloved,life,lifetime,living,aliveness,europe,european,europeans,uefa,groningen,amsterdam,batavia,djakarta,blue,colored,blueish,bluish,happy,glad,pleased,fortunate,leader,drawing,card,loss,leader,leadership,true,genuine,real,truthful,la,lah,lanthanum,atomic,number,57,bike,bicycle,motorcycle,pedal,cycling,biking,cyclist,bicycling,university,faculty,undergraduate,campus,story,tale,narrative,chronicle,candles,candela,cd,taper,full,complete,entire,fully,yygrworkshop,pop,pop,music,nonclassical,soda,pop,vote,ballot,balloting,voter,turnout,house,mansion,home,family,christmas,xmas,yule,yuletide,faire,laissez,savoir,fair,greekislands,netherlands,holland,the,netherlands,germany,give,make,hold,impart,crete,create,ridge,spine,gotcha,bingo,okay,alright,ride,drive,sit,mount,email,netmail,e-mail,electronic,mail,smile,grin,grinning,smirk,credits,recognition,quotation,citation,italy,italia,spain,italians,easy,simple,effortless,uncomplicated,future,upcoming,next,forthcoming,upcycle,origami,decoupage,crochet,calligraphy,greeks,hellene,grecian,hellenic,fun,entertaining,merriment,amusing,porto,santo,riche,rico,alex,patrick,david,alexander,summer,summertime,summery,spring,island,archipelago,isle,peninsula,long,lengthy,prolonged,lasting,cards,card,game,coupons,postcards,yygr,spain,espana,madrid,portugal,cabello,argandona,capello,hair,set,put,laid,dictated,community,local,public,profession,sets,put,laid,dictated,bodypainting,party,partisan,partizan,company,action,activity,activeness,legal,action,llama,lama,mud,dalai,summeringreece,se,southeast,selenium,atomic,number,34,thinking,reasoning,rational,thoughtful,start,begin,beginning,commence,mas,mamma,mama,mammy,graduation,commencement,ceremony,commencement,gradation,workshops,shop,seminars,seminar,mood,climate,temper,mode,gain,attain,earn,increase,beauty,stunner,peach,looker,instamood,visitgreece,rock,stone,rock,music,cradle,day,daylight,daytime,mean,solar,day,leave,depart,quit,let,perugia,brescia,catania,lecce,km,kilometer,kilometre,klick,ve,ive,ry,nai,fear,dread,concern,fearfulness,group,radical,communal,common,egberts,souse,winchester,won,south,korean,won,north,korean,won,triumphed,anniversary,day,of,remembrance,birthday,commemoration,vases,urns,ornaments,pots,douwe,menninga,lekker,weet,manne,baie,prosperous,prospering,thriving,flourishing,opportunity,chance,avenue,advantage,shape,form,physique,condition,berenshtein,wouter,frey,was,renowned,hands,workforce,custody,men,destiny,fate,luck,fortune,grasp,comprehend,grip,reach,tradition,custom,ritual,heritage,candle,candela,cd,taper,deadlines,timelines,timeline,timetables,fyrom,country,ifn,macedonia,delivery,deliverance,bringing,livery,columbia,columbia,river,capital,of,south,carolina,alberta,professor,lecturer,prof,scholar,haring,jink,jinks,nutmegged,groninger,evs,electron,volt,electric,electrical,economics,political,economy,economic,science,sociology,laureate,honorable,honourable,prizewinner,late,last,belated,latter,nobel,noble,prize,laureate,takes,hold,carry,get,year,yr,class,twelvemonth,stiglitz,dixit,agronomist,anchorman,joseph,samuel,jose,jacques,present,introduce,demonstrate,submit,torture,torment,agony,anguish,unconscionable,outrageous,exorbitant,extortionate,discover,find,learn,identify,hopeful,fortunate,rosy,wishful,museum,gallery,library,exhibition,oldest,first,largest,longest,project,design,plan,undertaking,recycle,reuse,reprocess,recyclable,prize,award,trophy,booty,enjoy,savor,revel,bask,newproductdevelopment,trekking,pony,tramping,journeying,advise,apprise,notify,suggest,hard,tough,difficult,tricky,policies,polices,strategies,initiatives,punitive,penal,correctional,penitentiary,regressive,retrogressive,retrograde,atavistic,measures,bill,measurement,quantify,austerity,asceticism,nonindulgence,frugality,unacceptable,intolerable,unsatisfactory,unaccepted,softwarerequirementsengineering,pitch,toss,slope,slant,accept,admit,assume,take,pure,unadulterated,undiluted,sheer,gorgeofthedead,knuckle,metacarpophalangeal,joint,knuckle,joint,buckle,nature,existence,macrocosm,cosmos,discovergreece,force,coerce,personnel,push,ideas,thought,mind,theme,place,spot,position,berth,july,september,december,february,civilization,civilisation,civilized,society,risks,danger,hazard,peril,bc,cbs,mcc,cmc,money,funds,cash,fund,democratic,republican,parliamentary,egalitarian,explanatory,informative,instructive,descriptive,strong,solid,robust,strengthened,possibility,hypothesis,theory,opening,open,ajar,wide,free,contrast,counterpoint,demarcation,line,depression,depressive,disorder,slump,economic,crisis,huge,big,immense,large,alternative,option,alternate,choice,photograph,photo,shoot,snap,swgro,carry,take,hold,transmit,capture,seize,captivate,catch,astonishing,astounding,staggering,amazing,terms,price,footing,damage,troika,trinity,trio,threesome,insanity,madness,lunacy,craziness,approval,approving,blessing,commendation,rejection,rejecting,rebuff,reject,lane,roadway,westbound,carriageway,expecting,anticipating,hoping,predicting,product,merchandise,production,wares,cooking,cookery,cuisine,culinary,art,night,nighttime,dark,nox,cried,wept,sobbed,screamed,onion,allium,cepa,onion,plant,cream,poem,verse,form,poetry,sonnet,preparing,readying,prepping,preparation,santas,christmas,xmas,claus,secret,clandestine,covert,confidential,figure,estimate,calculate,see,dutchies,abbeys,allied,arias,real,genuine,really,actual,sinterklaas,saint,nicholas,claus,religious,holiday,lia,cer,dem,pag,celebrating,commemorating,celebrations,commemorates,weekend,week,holiday,trip,tree,tree,diagram,conifer,shrub,hague,haile,haig,haag,lots,oodles,scads,heaps,helpful,useful,instrumental,accommodating,speculoos,chocolate,cocoa,coffee,hot,chocolate,beer,suds,lager,drink,jenever,hollands,alcohol,alcoholic,beverage,drink,intoxicant,task,chore,undertaking,job,pro,professional,favoring,affirmative,fill,occupy,cram,fulfill,break,crack,burst,recess,driving,motoring,dynamic,impulsive,sake,saki,interest,reason,god,deity,immortal,idol,eco,ecological,ecology,green,hey,yeah,huh,hmm,creative,imaginative,inventive,ingenious,iv,quaternary,quaternion,quadruplet,stop,halt,cease,quit,alkmaar,groningen,eindhoven,dortmund,days,years,weeks,months,trip,jaunt,travel,tripper,amersfoort,haarlem,nijmegen,maastricht,lived,resided,reside,stayed,upside,top,top,side,upper,side,room,board,way,elbow,room,turned,revolved,reversed,soured,amazing,awesome,astonishing,impressive,simply,merely,plainly,just,rotterdam,antwerp,europoort,amsterdam,dinner,dinner,party,brunch,supper,groceries,errands,food,grocers,cart,pushcart,handcart,haul,fit,suited,accommodate,suitable,building,construction,edifice,augmenting,cherry,ruby,cerise,colored,kingdom,realm,land,king,results,consequence,outcome,upshot,initiative,inaugural,enterprise,first,step,cuellar,lopez,former,said,meat,marrow,pith,substance,android,humanoid,mechanical,man,droid,ll,aliquots,dlc,cool,discovered,revealed,observed,disclosed,established,recognized,proven,entrenched,science,skill,scientific,discipline,scientific,knowledge,computer,calculator,figurer,estimator,area,region,country,expanse,women,men,womens,girls,inspire,invigorate,enliven,exhort,global,worldwide,world,international,running,moving,working,track,acm,agr,aiee,amc,anatomy,physique,flesh,shape,keith,kate,perry,kei,wrong,incorrect,amiss,haywire,grey,gray,greyish,hoar,scandal,outrage,dirt,malicious,gossip,sweetheart,sweetie,stunner,looker,contest,competition,contend,repugn,nice,good,decent,pleasant,einstein,albert,einstein,bose,buddy,albert,kent,alberta,hugh,brander,marketer,vidi,marketeer,google,valley,adsense,adwords,united,unified,cohesive,concerted,digital,digitization,digitized,electronic,newcastle,newcastle-upon-tyne,valenciennes,lyme,tynemouth,watchet,consett,banstead,news,tidings,word,newsworthiness,survey,study,review,view,breaking,breakage,shattering,smashing,winsum,autumn,fall,spring,summer,colors,colours,hues,shades,nijmegen,utrecht,arnhem,eindhoven,black,colored,negro,dark,skies,clouds,airspace,heavens,newspaper,paper,newsprint,newspaper,publisher,craziness,foolishness,daftness,folly,swan,stray,cast,drift,social,societal,cultural,interpersonal,cacin,closer,nearer,near,nigher,emoticon,grin,smiley,exclamation,grin,smile,smiling,smirk,debts,indebtedness,debtors,liabilities,billions,millions,trillions,zillions,ups,ahead,upwards,upward,reading,recitation,interpretation,version,california,golden,state,nevada,cali,wild,feral,untamed,undomesticated,merry,jolly,joyous,festive,meet,fulfill,satisfy,converge,overdose,poisoning,paracetamol,intoxication,ciao,aloha,arrivederci,hello,years,days,age,long,time,reunion,reunification,reuniting,togetherness,terribly,awfully,frightfully,abysmally,beach,seashore,shoreline,seaside,sea,ocean,oceanic,seafaring,sun,sunlight,sunshine,sunbathe,sunshine,sun,sunlight,fair,weather,eternal,everlasting,perpetual,immortal,cuteness,prettiness,adorableness,sexiness,stereotypes,pigeonhole,stamp,prejudices,hasta,upto,otro,until,gender,sexuality,sex,grammatical,gender,put,lay,set,place,living,life,livelihood,surviving,shallow,shoal,superficial,surface,great,good,big,neat,depths,profundity,astuteness,profoundness,mermaid,siren,burglar,horn,takemeback,vacation,holiday,holidays,getaway,laugh,joke,laughter,jest,wrinkles,crinkle,scrunch,rumple,rianxo,luego,hasta,adios,later,mirth,hilarity,glee,gleefulness,explored,discussed,researched,studied,gastronomia,beautiful,gorgeous,lovely,ravishing,word,phrase,watchword,tidings,listen,hear,heed,mind,don,wear,assume,put,on,matter,issue,topic,thing,child,baby,boy,girl,reason,cause,grounds,argue,gettin,completely,receive,home,shades,sunglasses,dark,glasses,hues,buddy,pal,chum,brother,glad,happy,thankful,grateful,team,squad,team,up,league,youngest,littlest,smallest,junior,independent,autonomous,unaffiliated,nonpartisan,country,nation,state,commonwealth,newborn,neonate,young,immature,chilling,scary,shivery,alarming,road,route,traveling,roadworthy,delicacies,goodies,foodstuffs,snacks,galician,galicia,gallego,gallegos,hunganol,favorite,popular,loved,darling,santiago,santiago,de,cuba,capital,of,chile,zamora,laughter,laugh,chuckles,laughs,ictpropday,suggestions,hint,proposition,mesmerism,pregraduationselfie,hans,michael,jurgen,joachim,travel,trip,journey,jaunt,remote,inaccessible,outback,unaccessible,lands,acres,demesne,farming,roads,roadstead,highways,roadways,roam,wander,rove,ramble,float,drift,swim,blow,fly,pilot,flee,wing,breathe,respire,emit,rest,move,go,proceed,propel,silly,ridiculous,goofy,foolish,andersen,hans,christian,andersen,andersson,lund,girlfriends,girl,lady,friend,pals,ii,cardinal,two,2,vol,vols,eds,volumes,graduationselfie,drinkwine,winelover,winery,wine,maker,vineyard,wine,bottle,jar,flask,vial,glass,glaze,spyglass,looking,glass,tech,technical,school,technology,technologies,continued,continual,ongoing,persisted,christian,christlike,christly,christianity,photo,photograph,exposure,pictures,proposers,mover,advocates,applicants,skg,dreamworks,animation,animator,ict,acacia,believe,cci,research,inquiry,search,explore,winds,curve,hoist,fart,os,operating,system,osmium,bone,representing,comprising,representation,consisting,livestage,livemusic,unplugged,plugged,disconnected,removed,stay,remain,continue,stick,makedoniapalace,wineplus,winetasting,ilias,alias,elias,menopause,prewed,reddress,womaninred,red,crimson,colored,scarlet,wear,don,outwear,clothing,doubt,question,uncertainty,dubiety,join,unite,connect,joint,wanna,gotta,gonna,want,treat,handle,regale,address,trick,magic,conjuring,trick,legerdemain,dimitriadis,tour,circuit,spell,enlistment,tastes,savor,savour,mouthful,hope,promise,desire,trust,experimentize,blanchir,vieillir,today,now,nowadays,tomorrow,study,survey,report,examine,zakopane,wadowice,culture,acculturation,cultural,ethos,gifts,endow,endowment,giving,decoration,ornament,ornamentation,laurel,wreath,crafts,craftsmanship,craftiness,workmanship,paper,newspaper,report,newspaper,publisher,magazine,mag,cartridge,clip,br,bromine,atomic,number,35,gt,fan,devotee,afficionado,lover,fanzine,zine,webzine,hotspur,turn,go,bend,convert,actions,activity,activeness,legal,action,international,global,foreign,multinational,young,teenage,teenaged,youth,participation,involvement,engagement,participating,equal,equivalent,same,equalize,policy,insurance,insurance,policy,polices,danger,peril,risk,threat,puts,lay,set,place,nuit,blackness,dark,nighttime,ler,lir,juillet,novembre,national,domestic,public,general,pleasures,joy,delight,pleasance,insane,crazy,lunatic,bonkers,roll,wrap,curl,revolve,exciting,exhilarating,thrilling,electrifying,panda,giant,panda,panda,bear,ailuropoda,melanoleuca,bored,tired,uninterested,blase,featured,faced,conspicuous,obvious,thoughts,idea,opinion,view,gems,jewel,gemstone,treasure,centre,center,pore,eye,asha,krishna,jain,kala,courses,path,track,class,small,tiny,minuscule,miniscule,lumi,phos,-1,-10,dreams,daydream,aspiration,ambition,sweet,sweetish,sugary,fragrant,indigo,anil,indigo,plant,indigofera,tinctoria,ain,personal,own,ayin,dream,daydream,aspiration,ambition,forgotten,unnoticed,disregarded,lost,revolving,rotating,turning,moving,door,doorway,threshold,room,access,jour,du,ney,daily,au,astronomical,unit,atomic,number,79,gold,jusqu,agency,bureau,authority,office,suspension,hiatus,reprieve,interruption,flavorsome,flavourful,flavoursome,tasteful,sino,hua,chinese,wah,graphics,artwork,art,nontextual,matter,kioutsioukis,stavros,hadgi,nikos,nikolaos,sketch,cartoon,outline,vignette,lostre,naked,nude,unclothed,bare,world,globe,global,worldwide,traiining,labrar,de,diethylstilbestrol,diethylstilboestrol,stilbestrol,seda,7,cog,03,el,alt,altitude,elevation,lekkas,mo,molybdenum,atomic,number,42,sist,novia,said,nuestra,lourdes,zaragoza,dice,cube,craps,luck,nutshell,essence,synopsis,summarize,august,venerable,lordly,honorable,hilly,mountainous,cragged,craggy,flat,thin,unexciting,dull,destinations,terminus,goal,address,ambrosial,heavenly,tasteful,ambrosian,cuisine,cooking,cookery,culinary,art,tasos,celebration,festivity,solemnization,festivities,garc,thought,idea,opinion,view,kitos,fanzin,yygrworkshops,fresh,new,refreshed,refreshing,brand,marque,label,stain,equalitygr,exceptions,exclusion,exemptions,provisos,partnership,collaboration,partnering,alliance,civil,civilian,civilized,civic,equality,equivalence,par,emancipation,supports,backing,funding,endorse,street,sidewalk,boulevard,alley,environment,surroundings,environs,surround,thessalsoniki,surprises,storm,surprisal,shocks,tre,um,tso,centre,los,las,luz,luis,percussion,drumming,fist,beat,krousi,performances,execution,doing,functioning,june,september,april,july,friday,fri,monday,tuesday,body,torso,trunk,consistence,human,mortal,anthropomorphic,manlike,cs,caesium,cesium,atomic,number,55");*/
/*print_r(get_likes($fb,$since));
print_r(get_posts($fb,$since));*/

?>
