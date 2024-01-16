module.exports = on => {
    on("task", {
        generateOTP: require("cypress-otp")
    });
};
