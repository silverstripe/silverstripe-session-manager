function LoginSession(loginSession) {
    let lastAccessedElapsed = moment.utc(loginSession.LastAccessed).fromNow();

    return (
        <div>
            <div>{loginSession.UserAgent}</div>
            {loginSession.IsCurrent && <strong className={"text-success"}>Current</strong>}
            <div className="text-muted">
                {loginSession.IPAddress}
                {!loginSession.IsCurrent && ", last active " + lastAccessedElapsed}
            </div>
            {!loginSession.IsCurrent && <a href={"#"}>Log Out</a>}
        </div>
    );
}

const LoginSessionShape = PropTypes.shape({
    ID: PropTypes.number,
    IPAddress: PropTypes.string,
    IsCurrent: PropTypes.bool,
    UserAgent: PropTypes.string,
    Persistent: PropTypes.number,
    Member: PropTypes.object,
    LastAccessed: PropTypes.string,
});

export default LoginSession;

export {LoginSessionShape};
