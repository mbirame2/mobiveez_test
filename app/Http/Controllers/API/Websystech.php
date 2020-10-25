<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Auth; 
use Validator;
use \Osms\Osms;
use App\confirmphone;
use Twilio\Rest\Client;


class Websystech extends Controller
{
    
    public $successStatus = 200;
/** 
     * login api 
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function login(){ 
        if(Auth::attempt(['telephone' => request('telephone'), 'password' => request('password')])){ 
            $user = Auth::user(); 
            $token =  $user->createToken('MyApp')->accessToken; 
            return response()->json( [$token,$user]); 
        } 
        else{ 
            return response()->json(['error'=>'Unauthorised, en cas de soucis contactez l administrateur WEBSYSTECH'], 401); 
        } 
    }

    /** 
     * login api 
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function testphonenumber(Request $request){ 
        $sid    = "ACe987fd4b9d77839dd1902409810471ec";
        $token  = "77453c10dc7078c90a16848f3ee8952f";
        $twilio = new Client($sid, $token);
        $code=rand(202, 10000);
     
        $message = $twilio->messages
                          ->create("+221777387073", // to
                                   [
                                       "body" => "Le code de vérification pour l'inscription sur WebsystechTest est: '$code'",
                                       "from" => "+18173832236",
                                       "statusCallback" => "http://postb.in/1234abcd"
                                   ]
                          );

        
        ;
           
           
            $trie= new confirmphone();
       
            $trie->telephone="221777387073";
            $trie->code=$code;
            $trie->statut="en attente de vérification";
            $trie->save();
            return response()->json(['success'=>'Inscrivez vous maintenant en saisissant le mot de passe envoyé au +221777387073'], $this->successStatus); 

        
    }



    /** 
     * Register api 
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function register(Request $request) 
    { 
        $validator = Validator::make($request->all(), [ 
            'nom' => 'required', 
            'telephone' => 'required', 
            'code'=> 'required', 
            'password' => 'required', 
            'c_password' => 'required|same:password', 
            'photo'  => 'required|mimes:png,jpg|max:2048',        ]);
        if ($validator->fails()) { 
            return response()->json(['error'=>$validator->errors()], 401);            
        }
        $input = $request->all(); 
            //store file into document folder
            $article = confirmphone::where('telephone',$input['telephone'])->first();
            if($article->code!=$input['code'] ){
                return response()->json(['error'=>"Le code de vérification saisi est incorrecte"], 401);            
            }else if(!$article){
                return response()->json(['error'=>"Aucun code n'a été généré sur ce numéro. Veillez verifier à nouveau votre numero"], 401);            
            }
            if($request->hasFile('photo') ){
                $image_name = $request->file('photo')->getClientOriginalName();
       $filename = pathinfo($image_name,PATHINFO_FILENAME);
       $image_ext = $request->file('photo')->getClientOriginalExtension();
       $fileNameToStore = $filename.'-'.time().'.'.$image_ext;
       $path =  $request->file('photo')->storeAs('public/images',$fileNameToStore);
              }
         $input['photo'] =   $fileNameToStore;
        $input['password'] = bcrypt($input['password']); 
        $article->statut='validé';
        $article->save();
        $user = User::create($input); 

        $success['message'] =  'Code vérifié. Utilisateur enregistré avec succés';
        $success['token'] =  $user->createToken('MyApp')->accessToken; 
        
        return response()->json(['success'=>$success], $this->successStatus); 

    }
}
