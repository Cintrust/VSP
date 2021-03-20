<?php

namespace App\Http\Requests;

use Illuminate\Auth\Access\AuthorizationException;

class TeamApiRequest extends AdminApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
//        check if current user is admin or is the team owner
        return parent::authorize() ||
            $this->route("team")->user_id === $this->user()->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }

    /**
     * Handle a failed authorization attempt.
     *
     * @return void
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    protected function failedAuthorization()
    {
        if (!parent::authorize()){
            parent::failedAuthorization();
        }
        throw new AuthorizationException("This team does not belong to you");
    }
}
