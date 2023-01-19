<?php

namespace App\Http\Controllers;

use App\Models\CommentRate;
use App\Repositories\CommentRateRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentRateController extends Controller
{
    protected CommentRateRepository $commentRateRepository;

    public function __construct(CommentRateRepository $commentRateRepository)
    {
        $this->commentRateRepository = $commentRateRepository;
    }

    /**
     * Toggle a Post like resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request) : JsonResponse
    {
        $this->validate($request, [
            'commentId' => ['required', 'int', 'exists:comments,id'],
            'value'     => ['required', 'int'],
        ]);

        $rateByMe = $this->commentRateRepository->findOneBy(
            [
                ['comment_id', $request->get('commentId')],
                ['user_id', Auth::user()->getId()],
                ['user_role', Auth::user()->getRole()]
            ],
            false
        );

        if ($rateByMe) {
            $commentRate = $rateByMe;
        }

        if (!$rateByMe || $request->get('value')) {
            $commentRate = $commentRate ?? new CommentRate();
            $commentRate->commentId = $request->get('commentId');
            $commentRate->value = $request->get('value');
            $commentRate->userRole = $request->user()->getRole();
            $commentRate->userId = $request->user()->getId();
            $commentRate->save();

            return new JsonResponse($commentRate->value, JsonResponse::HTTP_OK);
        }

        $rateByMe->delete();

        return new JsonResponse(0, JsonResponse::HTTP_OK);
    }
}
