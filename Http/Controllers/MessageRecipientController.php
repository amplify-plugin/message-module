<?php

namespace Amplify\System\Message\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageRecipientController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(string $userType, Request $request): JsonResponse
    {
        $is_customer = $request->boolean('as_customer');

        switch ($userType) {
            case 'user':
                $recipients = User::when(! $is_customer, function (Builder $builder) {
                    return $builder->whereNotIn('id', [backpack_user()->id]);
                })->get();
                break;
            case 'contact':
                $recipients = Customer::when($is_customer, function (Builder $builder) {
                    $builder->where('id', customer()->id);
                })->with(['contacts' => function ($builder) use ($is_customer) {
                    $builder->when($is_customer, fn ($query) => $query->where('id', '!=', customer(true)->id));
                }])->get();
                break;
            default:
                $recipients = [];
                break;
        }

        return response()->json(['type' => $userType, 'account' => $recipients], 200);
    }
}
