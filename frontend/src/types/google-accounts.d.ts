declare namespace google.accounts.id {
    interface IdConfiguration {
        client_id: string;
        callback: (response: CredentialResponse) => void;
        auto_select?: boolean;
        cancel_on_tap_outside?: boolean;
        context?: 'signin' | 'signup' | 'use';
        itp_support?: boolean;
        login_uri?: string;
        native_callback?: (response: CredentialResponse) => void;
        nonce?: string;
        prompt_parent_id?: string;
        state_cookie_domain?: string;
        ux_mode?: 'popup' | 'redirect';
        allowed_parent_origin?: string | string[];
        intermediate_iframe_close_callback?: () => void;
        use_fedcm_for_prompt?: boolean;
    }

    type SelectBy =
        | 'auto'
        | 'user'
        | 'user_1tap'
        | 'user_2tap'
        | 'btn'
        | 'btn_confirm'
        | 'btn_add_session'
        | 'btn_confirm_add_session';

    interface CredentialResponse {
        credential: string;
        select_by: SelectBy;
        clientId?: string;
    }

    interface GsiButtonConfiguration {
        type: 'standard' | 'icon';
        theme?: 'outline' | 'filled_blue' | 'filled_black';
        size?: 'large' | 'medium' | 'small';
        text?: 'signin_with' | 'signup_with' | 'continue_with' | 'signin';
        shape?: 'rectangular' | 'pill' | 'circle' | 'square';
        logo_alignment?: 'left' | 'center';
        width?: number;
        locale?: string;
    }

    function initialize(config: IdConfiguration): void;
    function prompt(momentListener?: (promptMomentNotification: PromptMomentNotification) => void): void;
    function renderButton(parent: HTMLElement, options: GsiButtonConfiguration): void;
    function disableAutoSelect(): void;
    function storeCredential(credential: { id: string; password: string }, callback?: () => void): void;
    function cancel(): void;
    function revoke(hint: string, callback?: (response: RevocationResponse) => void): void;

    type NotDisplayedReason =
        | 'browser_not_supported'
        | 'invalid_client'
        | 'missing_client_id'
        | 'opt_out_or_no_session'
        | 'secure_http_required'
        | 'suppressed_by_user'
        | 'unregistered_origin'
        | 'unknown_reason';

    interface PromptMomentNotification {
        isDisplayMoment(): boolean;
        isDisplayed(): boolean;
        isNotDisplayed(): boolean;
        getNotDisplayedReason(): NotDisplayedReason;
        isSkippedMoment(): boolean;
        getSkippedReason(): 'auto_cancel' | 'user_cancel' | 'tap_outside' | 'issuing_failed';
        isDismissedMoment(): boolean;
        getDismissedReason(): 'credential_returned' | 'cancel_called' | 'flow_restarted';
        getMomentType(): 'display' | 'skipped' | 'dismissed';
    }

    interface RevocationResponse {
        successful: boolean;
        error?: string;
    }
}

interface Window {
    google?: {
        accounts: {
            id: typeof google.accounts.id;
        };
    };
}
