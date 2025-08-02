export const getStartOfDay = (date) => {
    return new Date(date.getFullYear()
    ,date.getMonth()
    ,date.getDate()
    ,0,0,0);
}

export const getEndOfDay = (date) => {
    return new Date(date.getFullYear()
    ,date.getMonth()
    ,date.getDate()
    ,23,59,59,999);
}

export const getStartOfMonth = (date) => {
    return new Date(date.getFullYear()
    ,date.getMonth()
    ,1
    ,0,0,0);
}

export const getEndOfMonth = (date) => {
    return new Date(date.getFullYear()
    ,date.getMonth() + 1
    ,0
    ,23,59,59,999);
}