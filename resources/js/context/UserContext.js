import React, {useState} from "react";

const UserContext = React.createContext();

export const UserProvider = (props) => {
    const [user, setUser] = useState(null);

    return <UserContext.Provider value={{user, setUser}} {...props}/>;
}

export default UserContext;