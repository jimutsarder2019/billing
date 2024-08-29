<?php

namespace App\Http\Controllers\Ticket;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Ticket;

class TicketController extends Controller
{
    public $model;
    public $auth;
    function __construct()
    {
        $this->model = new Ticket();
        $this->middleware(function ($request, $next) {
            $this->auth = auth()->user();
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
            if (!$this->auth->can('Ticket')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
            $data =  $this->model
                ->with('ticket_category', 'customer')
                ->latest()
                ->paginate($request->itemsPerPage ? ($request->itemsPerPage == 0 ? $this->model->select('id')->count() : $request->itemsPerPage) : 10);
            $data = [
                'data' => $data,
                'pending' => $this->model->select('id')->where('status', 'pending')->count(),
                'processing' => $this->model->select('id')->where('status', 'processing')->count(),
                'completed' => $this->model->select('id')->where('status', 'completed')->count(),
            ];
            return view("content/ticket/index", $data);
        } catch (Exception $e) {
            dd($e);
            ////=======handle DB exception error==========
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
        if (!$this->auth->can('Ticket Add')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        return view('content/ticket/create-ticket');
    }

    /**
     *👉 @param Request $request
     * Store a newly created resource in storage.
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        if (!$this->auth->can('Ticket Edit')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $request->validate([
            'name'      => "required",
            'phone'     => "required",
            'category'  => "required",
            'priority'  => "required",
        ]);
        ///=======when validation faild================
        DB::beginTransaction();
        try {
            $last_tkt_id    = $this->model->select('id')->latest()->first();
            $ticket_no      = $last_tkt_id !== null ? $last_tkt_id->id + 1 . "-" . $request->customer : '1-' . $request->customer;
            // store data
            $data               = $this->model;
            $data->name         = $request->name;
            $data->phone        = $request->phone;
            $data->ticket_no    =  $ticket_no;

            $data->ticket_category_id = $request->category;
            $data->customer_id   = $request->customer;
            $data->manager_id       = $this->auth->id;
            $data->priority      = $request->priority;
            $data->note  = $request->note;
            $data->save();
            DB::commit();
            notify()->success('Ticket Create Successfully');
            return redirect()->route('ticket.index');
        } catch (Exception $e) {
            dd($e);
            DB::rollBack();
            ////=======handle DB exception error==========
            return error_message($e->getMessage(), $e->getCode());
        }
    }


    /**
     * 👉 customer ticket
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function get_customer_ticket($id)
    {
        try {
            $data =  $this->model->with('ticket_category')->where('customer_id', intval($id))->latest()->get();
            return response()->json(["success" => true, "data" => $data]);
        } catch (Exception $e) {
            ////=======handle DB exception error==========
            return error_message('Database Exception Error', $e->getMessage(), $e->getCode());
        }
    }

    /**
     * 👉 change status spesific item.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request)
    {
        if (!$this->auth->can('Ticket Change Status')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        ///=======when validation faild================
        try {
            if ($request->status) {
                $data = $this->model->find($id);
                $data->update(['status' => $request->status]);
                notify()->success('Changed Sucessfully');
                return back();
            } else {
                $data = $this->model->with('ticket_category', 'manager')->find($id);
                return view('content/ticket/view-ticket', compact('data'));
            }
        } catch (Exception $e) {
            ////=======handle DB exception error==========
            return error_message('Database Exception Error', $e->getMessage(), $e->getCode());
        }
    }

    /**
     *  👉 Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!$this->auth->can('Ticket Edit')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        try {
            $data =  Ticket::find($id);
            // dd($data );
            return view('content/ticket/edit-ticket', compact('data'));
        } catch (Exception $e) {
            ////=======handle DB exception error==========
            return error_message('Database Exception Error', $e->getMessage(), $e->getCode());
        }
    }

    /**
     * 👉 Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!$this->auth->can('Ticket Edit')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        try {
            $request->validate([
                'name'      => "required",
                'phone'     => "required",
                'category'  => "required",
                'priority'  => "required",
            ]);
            DB::beginTransaction();
            try {
                $data               = $this->model->find($id);
                $data->name         = $request->name ?? $data->name;
                $data->phone        = $request->phone ?? $data->phone;
                $data->ticket_category_id = $request->category ?? $data->ticket_category_id;
                $data->priority     = $request->priority;
                $data->note         = $request->note;
                $data->save();
                DB::commit();
                notify()->success("Successfully",);
                return redirect()->route('ticket.index');
            } catch (Exception $e) {
                DB::rollBack();
                ////=======handle DB exception error==========
                return error_message($e->getMessage(), $e->getCode());
            }
        } catch (Exception $e) {
            ////=======handle DB exception error==========
            return error_message('Database Exception Error', $e->getMessage(), $e->getCode());
        }
    }

    /**
     * 👉 Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!$this->auth->can('Ticket Delete')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        try {
            $this->model->find($id)->delete();
            notify()->success("Delete Successfully");
            return back();
        } catch (Exception $e) {
            ////=======handle DB exception error==========
            return error_message('Database Exception Error', $e->getMessage(), $e->getCode());
        }
    }

    /**
     *👉  Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function action_selected(Request $request)
    {
        try {
            if ($request->action_for == 'active' || $request->action_for == 'inactive') {
                if (!$this->auth->can('ticket_change_status')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
                $status = $request->action_for == 'active' ? 1 : 0;
                $this->model->whereIn('id', $request->selected_items)->update(['status' => $status]);
            } else {
                if (!$this->auth->can('ticket_delete')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
                foreach ($request->selected_items as $key => $id) {
                    $data = $this->model->with('zone')->find($id);
                    if (count($data->zone) <= 0) {
                        $data->delete();
                    }
                }
            }
            notify()->success(ucwords($request->action_for) . " Selected Successfully");
        } catch (\Throwable $th) {
            return error_message('Database Exception Error', $th->getMessage(), $th->getCode());
        }
    }
}
