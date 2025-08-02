import axios from "axios";

const Api = (url, params, method = "GET", apiConfig = {}) => {
    const token = localStorage.getItem("token");

    let config = {
        url: url,
        method,
        headers: {
            Authorization: "Bearer " + token,
        },
        ...(method == "GET" ? { params: params } : { data: params }),
    };

    const defaultErrorFunction = (error) => {
        if (error.response.status == 401) {
            localStorage.removeItem("token");
            window.location = "/login";
        }
        if (error.response.status == 422) {
            console.log(error.response);
            alert(error.response.data.error);
        }

        return error;
    };

    return axios(config).catch(apiConfig.errorFn || defaultErrorFunction);
};

export default Api;
