<?php

namespace App\Http\Controllers\Ticket;

use App\Http\Controllers\Controller;
use App\Models\TicketCategory;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TicketCategoryController extends Controller
{

    public $model;
    public $auth_user;
    public function __construct()
    {
        $this->model = new TicketCategory();
        $this->middleware(function ($request, $next) {
            $this->auth_user = auth()->user();
            return $next($request);
        });
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            if (!$this->auth_user->can('Ticket Category')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
            $data =  $this->model
            ->select([
                    'id',
                    'name',
                    'priority',
                    'status',
                    'updated_at',
                    ])
                    ->when($request->date_range, function ($q) use ($request) {
                        return $q->whereBetween('updated_at', date_range_search($request->date_range));
                    })
                ->when($request->orderBy, function ($q) use ($request) {
                    return $q->orderBy('id', $request->orderBy);
                })
                ->when($request->searchQuery, function ($q) use ($request) {
                    return $q->where('name', 'LIKE', "%$request->searchQuery%");
                })
                ->latest()
                ->paginate($request->itemsPerPage ? ($request->itemsPerPage == 0 ? $this->model->select('id')->count() : $request->itemsPerPage) : 10);
                $data = [
                    'data' => $data,
                    'active' => $this->model->select('id')->where('status', 'false')->count(),
                    'inactive' => $this->model->select('id')->where('status', 'true')->count(),
                ];
            return view("content/ticket/ticketcategory/index", $data);
        } catch (Exception $e) {
            return error_message('Database Exception Error', $e->getMessage(), $e->getCode());
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('content/ticket/ticketcategory/addEdit');
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!$this->auth_user->can('Ticket Category Add')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $request->validate([
            'name'      => "required|unique:ticket_categories,name",
            'priority'  => "required",
        ]);
        ///=======when validation faild================
        try {
            // if (!$this->auth_user->can('ticket_category_add')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
            $data = $this->model;
            $data->name     = $request->name;
            $data->priority = $request->priority;
            $data->save();
            notify()->success("Data Saved Successfully.");
            return redirect()->route('ticketcategory.index');
        } catch (Exception $exception) {
            error_message($exception->getMessage());
        }
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!$this->auth_user->can('ticket_category_change_status')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        try {
            $data = TicketCategory::where('id', $id)->update(['status' => DB::raw("IF(status = 1, 0 ,1)")]);
            notify()->success("Status Change Successfully");
            return back();
        } catch (Exception $e) {
            ////=======handle DB exception error==========
            return error_message('Database Exception Error', $e->getMessage(), $e->getCode());
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!$this->auth_user->can('Ticket Category Edit')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        try {
            $data = $this->model->find($id);
            if (!$data)  return abort(404);
            return view('content/ticket/ticketcategory/addEdit', compact('data'));
        } catch (\Throwable $th) {
            notify()->warning($th->getMessage());
            return back();
        }
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!$this->auth_user->can('Ticket Category Edit')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $request->validate([
            'name'      => "required|unique:ticket_categories,name,$id",
            'priority'  => 'required',
        ]);
        
        // =======when validation faild================
        try {
            $data = TicketCategory::find($id);
            $data->update([
                'name'      => $request->name,
                'priority'  => $request->priority,
            ]);
            notify()->success("Update Successfully.");
            return redirect()->route('ticketcategory.index');
        } catch (Exception $exception) {
            dd($exception);
            info($exception->getMessage());
            return error_message('Something went wrong!', $exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!$this->auth_user->can('Ticket Category Delete')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        try {
            $data = TicketCategory::with('tickets')->find($id);
            if ($data->tickets->count() > 0) {
                notify()->warning('Assigned Data has been present');
                return back();
            } else {
                $data->delete();
                notify()->success("Data Delete Successfully.");
                return back();
            }
        } catch (Exception $e) {
            dd($e);
            ////=======handle DB exception error==========
            return error_message('Database Exception Error', $e->getMessage(), $e->getCode());
        }
    }
}
