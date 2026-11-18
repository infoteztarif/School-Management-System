<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class SessionController extends Controller {
 public function __construct(){ $this->middleware(['auth','subscription.active']); }
 public function index(Request $request){$sessions=DB::table('tenant_user_sessions')->where('user_id',$request->user()->id)->latest('last_activity_at')->paginate(20);return $request->expectsJson()?response()->json($sessions):view('pages.account.sessions',compact('sessions'));}
 public function revoke(Request $request,$id){$session=DB::table('tenant_user_sessions')->where('id',$id)->where('user_id',$request->user()->id)->first();abort_unless($session,404);DB::table('tenant_user_sessions')->where('id',$id)->update(['revoked_at'=>now(),'updated_at'=>now()]);return back()->with('success','Session revoked.');}
}
