module.exports.getVariables = function getVariables(req, res) {
    const q = req.query.get;
    res.send({
        message: 'This is the mockup controller for getVariables'
    });
}

module.exports.postUserVariables = function postUserVariables(req, res) {
    res.send({
        message: 'This is the mockup controller for postUserVariables'
    });
}

