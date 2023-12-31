export class Alert {
    public id: string;
    public type: AlertType;
    public message: string;
    public autoClose: boolean;
    public keepAfterRouteChange: boolean;
    public fade: boolean;

    public constructor(init?:Partial<Alert>) {
        Object.assign(this, init);
    }
}

export enum AlertType {
    Success,
    Error,
    Info,
    Warning
}
