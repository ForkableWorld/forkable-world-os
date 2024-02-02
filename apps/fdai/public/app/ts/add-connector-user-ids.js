"use strict";
var __awaiter = (this && this.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
var __generator = (this && this.__generator) || function (thisArg, body) {
    var _ = { label: 0, sent: function() { if (t[0] & 1) throw t[1]; return t[1]; }, trys: [], ops: [] }, f, y, t, g;
    return g = { next: verb(0), "throw": verb(1), "return": verb(2) }, typeof Symbol === "function" && (g[Symbol.iterator] = function() { return this; }), g;
    function verb(n) { return function (v) { return step([n, v]); }; }
    function step(op) {
        if (f) throw new TypeError("Generator is already executing.");
        while (g && (g = 0, op[0] && (_ = 0)), _) try {
            if (f = 1, y && (t = op[0] & 2 ? y["return"] : op[0] ? y["throw"] || ((t = y["return"]) && t.call(y), 0) : y.next) && !(t = t.call(y, op[1])).done) return t;
            if (y = 0, t) op = [op[0] & 2, t.value];
            switch (op[0]) {
                case 0: case 1: t = op; break;
                case 4: _.label++; return { value: op[1], done: false };
                case 5: _.label++; y = op[1]; op = [0]; continue;
                case 7: op = _.ops.pop(); _.trys.pop(); continue;
                default:
                    if (!(t = _.trys, t = t.length > 0 && t[t.length - 1]) && (op[0] === 6 || op[0] === 2)) { _ = 0; continue; }
                    if (op[0] === 3 && (!t || (op[1] > t[0] && op[1] < t[3]))) { _.label = op[1]; break; }
                    if (op[0] === 6 && _.label < t[1]) { _.label = t[1]; t = op; break; }
                    if (t && _.label < t[2]) { _.label = t[2]; _.ops.push(op); break; }
                    if (t[2]) _.ops.pop();
                    _.trys.pop(); continue;
            }
            op = body.call(thisArg, _);
        } catch (e) { op = [6, e]; y = 0; } finally { f = t = 0; }
        if (op[0] & 5) throw op[1]; return { value: op[0] ? op[1] : void 0, done: true };
    }
};
Object.defineProperty(exports, "__esModule", { value: true });
var client_1 = require("@prisma/client");
var prisma = new client_1.PrismaClient();
function main() {
    return __awaiter(this, void 0, void 0, function () {
        var connectors, loginConnectors, connectorIds, connections, _a, _b, _c, _i, connectionsKey, connection, connector, connectorName, meta, _d, _e, _f, _g, metaKey, metaItem, connector_user_id, res;
        return __generator(this, function (_h) {
            switch (_h.label) {
                case 0: return [4 /*yield*/, prisma.connectors.findMany()];
                case 1:
                    connectors = _h.sent();
                    loginConnectors = connectors.filter(function (connector) {
                        return connector.name === "googleplus" ||
                            connector.name === "facebook" ||
                            connector.name === "twitter" ||
                            connector.name === "github";
                    });
                    connectorIds = loginConnectors.map(function (connector) { return connector.id; });
                    return [4 /*yield*/, prisma.connections.findMany({
                            where: {
                                connector_user_id: {
                                    equals: null
                                },
                                connector_id: {
                                    in: connectorIds
                                }
                            },
                            include: {
                                connector: true,
                                human: true
                            }
                        })];
                case 2:
                    connections = _h.sent();
                    _a = connections;
                    _b = [];
                    for (_c in _a)
                        _b.push(_c);
                    _i = 0;
                    _h.label = 3;
                case 3:
                    if (!(_i < _b.length)) return [3 /*break*/, 9];
                    _c = _b[_i];
                    if (!(_c in _a)) return [3 /*break*/, 8];
                    connectionsKey = _c;
                    connection = connections[connectionsKey];
                    connector = connection.connector;
                    connectorName = connector.name;
                    console.log("Connector name: ".concat(connectorName, " - ").concat(connection.human.email));
                    return [4 /*yield*/, prisma.wp_usermeta.findMany({
                            where: {
                                user_id: {
                                    equals: connection.user_id
                                },
                                meta_key: {
                                    contains: "%" + connectorName + "%"
                                }
                            }
                        })];
                case 4:
                    meta = _h.sent();
                    _d = meta;
                    _e = [];
                    for (_f in _d)
                        _e.push(_f);
                    _g = 0;
                    _h.label = 5;
                case 5:
                    if (!(_g < _e.length)) return [3 /*break*/, 8];
                    _f = _e[_g];
                    if (!(_f in _d)) return [3 /*break*/, 7];
                    metaKey = _f;
                    metaItem = meta[metaKey];
                    if (!(metaItem.meta_key === connectorName + "_connector_user_id")) return [3 /*break*/, 7];
                    connector_user_id = metaItem.meta_value;
                    return [4 /*yield*/, prisma.connections.update({
                            where: {
                                id: connection.id
                            },
                            data: {
                                connector_user_id: connector_user_id
                            }
                        })];
                case 6:
                    res = _h.sent();
                    console.log("Updated connection ".concat(connection.id, " with connector_user_id ").concat(metaItem.meta_value, ". Result: ").concat(res));
                    _h.label = 7;
                case 7:
                    _g++;
                    return [3 /*break*/, 5];
                case 8:
                    _i++;
                    return [3 /*break*/, 3];
                case 9: return [2 /*return*/];
            }
        });
    });
}
main()
    .then(function () { return __awaiter(void 0, void 0, void 0, function () {
    return __generator(this, function (_a) {
        switch (_a.label) {
            case 0: return [4 /*yield*/, prisma.$disconnect()];
            case 1:
                _a.sent();
                return [2 /*return*/];
        }
    });
}); })
    .catch(function (e) { return __awaiter(void 0, void 0, void 0, function () {
    return __generator(this, function (_a) {
        switch (_a.label) {
            case 0:
                console.error(e);
                return [4 /*yield*/, prisma.$disconnect()];
            case 1:
                _a.sent();
                process.exit(1);
                return [2 /*return*/];
        }
    });
}); });
//# sourceMappingURL=add-connector-user-ids.js.map