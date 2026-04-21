const RESULT_COPY = {
    en: {
        greeting: (name) => `Hey ${name}!`,
        subtitle: (platform) => ["What ", platform, " Says About You..."],
        safeSummary: "Overall your score is looking safe. We did not detect personal identifiable information in the posts scanned.",
        issueSummary: (tone, count, issues) => `Overall your score is ${tone}. We uncovered ${count} possible privacy issue${count === 1 ? "" : "s"}. The issues that stand out the most are ${issues}.`,
        noPosts: "No posts were flagged.",
        noPii: "No PII detected",
        scoreTitle: "Overall Score",
        labels: {
            1: "PII - Level 1",
            2: "PII - Level 2",
            3: "PII - Level 3",
            safe: "Safe"
        },
        tone: {
            1: "worth reviewing",
            2: "not looking good",
            3: "at critical risk"
        },
        legend: {
            1: "Amber alert: information that makes you a target for future scams or social engineering.",
            2: "Red alert: information that allows scammers to contact you directly or bypass basic security filters.",
            3: "Critical alert: information that allows direct theft of funds or long-term financial damage.",
            safe: "Posts where the scanner did not detect personal identifiable information."
        }
    },
    es: {
        greeting: (name) => `Hola ${name}!`,
        subtitle: (platform) => ["Lo que ", platform, " dice sobre ti..."],
        safeSummary: "Tu puntuacion general parece segura. No detectamos informacion de identificacion personal en las publicaciones analizadas.",
        issueSummary: (tone, count, issues) => `Tu puntuacion general ${tone}. Detectamos ${count} posible${count === 1 ? "" : "s"} riesgo${count === 1 ? "" : "s"} de privacidad. Los datos que mas destacan son ${issues}.`,
        noPosts: "No se marco ninguna publicacion.",
        noPii: "No se detecto IPI",
        scoreTitle: "Puntuacion General",
        labels: {
            1: "IPI - Nivel 1",
            2: "IPI - Nivel 2",
            3: "IPI - Nivel 3",
            safe: "Seguro"
        },
        tone: {
            1: "merece revision",
            2: "no se ve bien",
            3: "esta en riesgo critico"
        },
        legend: {
            1: "Alerta ambar: informacion que te convierte en objetivo de futuras estafas o ingenieria social.",
            2: "Alerta roja: informacion que permite a los estafadores contactarte directamente o saltarse filtros basicos de seguridad.",
            3: "Alerta critica: informacion que puede permitir el robo directo de fondos o dano financiero a largo plazo.",
            safe: "Publicaciones donde el escaner no detecto informacion de identificacion personal."
        }
    }
};

const RISK_DEFINITIONS = {
    FIRST_NAME: {
        level: 1,
        en: ["First Name", "By having your first name, scammers can personalize phishing emails or texts to make them look legitimate."],
        es: ["Nombre", "Al tener tu nombre, los estafadores pueden personalizar correos o mensajes de phishing para que parezcan legitimos."]
    },
    SCHOOL_NAME: {
        level: 1,
        en: ["School Name", "By knowing where you study, scammers can find you on other platforms or pretend to be a classmate or teacher."],
        es: ["Centro educativo", "Al saber donde estudias, los estafadores pueden encontrarte en otras plataformas o hacerse pasar por un companero o profesor."]
    },
    BIRTHPLACE: {
        level: 1,
        en: ["Birthplace", "By knowing where you were born, scammers can guess answers to common security questions."],
        es: ["Lugar de nacimiento", "Al saber donde naciste, los estafadores pueden adivinar respuestas a preguntas de seguridad comunes."]
    },
    SURNAME: {
        level: 1,
        en: ["Surname", "If your first name is leaked alongside your surname, scammers can link your social profiles and build an identity theft profile."],
        es: ["Apellido", "Si tu nombre se filtra junto con tu apellido, los estafadores pueden conectar tus perfiles y crear un perfil para suplantacion."]
    },
    MOBILE_NUMBER: {
        level: 2,
        en: ["Mobile Number", "By having your number, scammers can send fake account locked SMS alerts or attempt a SIM swap."],
        es: ["Numero movil", "Al tener tu numero, los estafadores pueden enviar SMS falsos de cuenta bloqueada o intentar un duplicado de SIM."]
    },
    ADDRESS: {
        level: 2,
        en: ["Address", "By knowing where you live, scammers can intercept bank letters or use your location in identity checks."],
        es: ["Direccion", "Al saber donde vives, los estafadores pueden interceptar cartas bancarias o usar tu ubicacion en comprobaciones de identidad."]
    },
    DATE_OF_BIRTH: {
        level: 2,
        en: ["Date of Birth", "Your birthdate is one of the most common details banks use to verify identity."],
        es: ["Fecha de nacimiento", "Tu fecha de nacimiento es uno de los datos mas usados por bancos para verificar identidad."]
    },
    BACS: {
        level: 2,
        en: ["BACS Details", "BACS transfer details can help scammers spoof your bank or a service you pay."],
        es: ["Detalles BACS", "Los detalles BACS pueden ayudar a estafadores a hacerse pasar por tu banco o un servicio que pagas."]
    },
    SORT_CODE: {
        level: 2,
        en: ["Sort Code", "A sort code identifies where you bank and can support unauthorized Direct Debit attempts."],
        es: ["Sort code", "Un sort code identifica donde tienes tu cuenta y puede facilitar intentos de domiciliacion no autorizada."]
    },
    IBAN: {
        level: 2,
        en: ["IBAN", "An IBAN identifies banking details that can support payment fraud attempts."],
        es: ["IBAN", "Un IBAN identifica datos bancarios que pueden facilitar intentos de fraude de pagos."]
    },
    NATIONAL_INSURANCE_NUMBER: {
        level: 3,
        en: ["National Insurance Number", "This can be used for long-term identity fraud, credit applications, or benefit claims in your name."],
        es: ["Numero de seguridad social", "Este dato puede usarse para fraude de identidad a largo plazo, credito o ayudas en tu nombre."]
    },
    BANK_ACCOUNT_NUMBER: {
        level: 3,
        en: ["Bank Account Number", "An account number can be used with other banking details to impersonate you or attempt fraudulent payments."],
        es: ["Numero de cuenta bancaria", "Un numero de cuenta puede usarse con otros datos bancarios para suplantarte o intentar pagos fraudulentos."]
    },
    DEBIT_CARD_NUMBER: {
        level: 3,
        en: ["Debit Card Number", "A debit card number gives scammers part of what they need for online purchases."],
        es: ["Numero de tarjeta", "Un numero de tarjeta da a los estafadores parte de lo necesario para compras online."]
    },
    CARD_EXPIRY: {
        level: 3,
        en: ["Card Expiry", "The expiry date confirms that your card is active and valid for transactions."],
        es: ["Caducidad de tarjeta", "La fecha de caducidad confirma que tu tarjeta esta activa y valida para transacciones."]
    },
    CARD_CVC: {
        level: 3,
        en: ["Card CVC", "The CVC can complete the final security step for many online purchases."],
        es: ["CVC de tarjeta", "El CVC puede completar el paso final de seguridad de muchas compras online."]
    }
};

const COMBO_RISKS = [
    {
        id: "FULL_IDENTITY_COMBO",
        required: ["FIRST_NAME", "SURNAME", "ADDRESS", "DATE_OF_BIRTH"],
        level: 2,
        en: ["Full Identity Combo", "Your first name, surname, address and date of birth appear together, enough for some credit or phone contract fraud attempts."],
        es: ["Combinacion de identidad completa", "Tu nombre, apellido, direccion y fecha de nacimiento aparecen juntos, suficiente para algunos intentos de credito o contrato telefonico fraudulento."]
    },
    {
        id: "CARD_COMBO",
        required: ["DEBIT_CARD_NUMBER", "CARD_EXPIRY", "CARD_CVC"],
        level: 3,
        en: ["Card Combo", "Your card number, expiry and CVC appear together, giving scammers enough data to spend money quickly."],
        es: ["Combinacion de tarjeta", "Tu numero de tarjeta, caducidad y CVC aparecen juntos, dando a estafadores datos suficientes para gastar dinero rapidamente."]
    },
    {
        id: "BANKING_COMBO",
        required: ["BANK_ACCOUNT_NUMBER", "SORT_CODE"],
        optional: ["FIRST_NAME", "SURNAME"],
        level: 3,
        en: ["Banking Combo", "Your account number and sort code appear with your name, which can support recurring Direct Debit fraud."],
        es: ["Combinacion bancaria", "Tu numero de cuenta y sort code aparecen con tu nombre, lo que puede facilitar fraude por domiciliacion recurrente."]
    }
];

function getLanguage() {
    return document.documentElement.lang === "es" ? "es" : "en";
}

function normalizeType(type) {
    return String(type).trim().toUpperCase().replace(/\s+/g, "_").replace(/[^A-Z0-9_]/g, "");
}

function extractTypes(finding) {
    const rawTypes = Array.isArray(finding.types)
        ? finding.types
        : String(finding.type || finding.flag || "").replace(/\band\b/gi, ",").split(",");

    return [...new Set(rawTypes.map(normalizeType).filter((type) => RISK_DEFINITIONS[type]))];
}

function getData() {
    const element = document.getElementById("scan-results-data");

    if (!element) {
        return { platform: "Social Media", profileName: "there", totalScanned: 0, findings: [] };
    }

    try {
        const data = JSON.parse(element.textContent);
        return {
            platform: data.platform || "Social Media",
            profileName: data.profileName || "there",
            totalScanned: Number.isFinite(data.totalScanned) ? data.totalScanned : null,
            findings: Array.isArray(data.findings) ? data.findings : []
        };
    } catch (error) {
        console.error("Could not parse scan results data.", error);
        return { platform: "Social Media", profileName: "there", totalScanned: 0, findings: [] };
    }
}

function enrichFindings(findings) {
    return findings.map((finding) => {
        const types = extractTypes(finding);
        const highestLevel = types.reduce((level, type) => Math.max(level, RISK_DEFINITIONS[type].level), 0);
        return { ...finding, types, highestLevel };
    });
}

function detectCombos(findings) {
    const types = new Set(findings.flatMap((finding) => finding.types));

    return COMBO_RISKS.filter((combo) => {
        const hasRequired = combo.required.every((type) => types.has(type));
        const hasOptional = !combo.optional || combo.optional.some((type) => types.has(type));
        return hasRequired && hasOptional;
    });
}

function strong(text) {
    const element = document.createElement("strong");
    element.textContent = text;
    return element;
}

function setSummary(data, risks, lang, copy) {
    const title = document.getElementById("scanner-results-title");
    const subtitle = document.getElementById("scanner-results-subtitle");
    const description = document.getElementById("scanner-results-description");

    if (title) {
        title.textContent = copy.greeting(data.profileName);
    }

    if (subtitle) {
        const [before, platform, after] = copy.subtitle(data.platform);
        subtitle.textContent = "";
        subtitle.append(before, strong(platform), ` ${after}`);
    }

    if (!description) {
        return;
    }

    description.textContent = "";

    if (risks.length === 0) {
        description.textContent = copy.safeSummary;
        return;
    }

    const highestLevel = risks[0].level;
    const topLabels = risks.slice(0, 3).map((risk) => risk.label).join(", ");
    const summary = copy.issueSummary(copy.tone[highestLevel], risks.length, topLabels);
    const parts = summary.split(topLabels);

    description.append(parts[0], strong(topLabels), parts[1] || "");
}

function setRiskList(risks, copy) {
    const list = document.getElementById("scanner-results-risk-list");

    if (!list) {
        return;
    }

    list.textContent = "";

    risks.slice(0, 3).forEach((risk) => {
        const item = document.createElement("li");
        item.append(strong(`${risk.label}: `), risk.summary);
        list.append(item);
    });
}

function getBadgeClass(level) {
    if (level === 3) return "risk-badge--level-three";
    if (level === 2) return "risk-badge--level-two";
    if (level === 1) return "risk-badge--level-one";
    return "risk-badge--safe";
}

function renderTable(findings, lang, copy) {
    const body = document.getElementById("scanner-results-table-body");

    if (!body) {
        return;
    }

    body.textContent = "";

    if (findings.length === 0) {
        const row = document.createElement("tr");
        const cell = document.createElement("td");
        cell.colSpan = 3;
        cell.textContent = copy.noPosts;
        row.append(cell);
        body.append(row);
        return;
    }

    findings.forEach((finding) => {
        const row = document.createElement("tr");
        const date = document.createElement("td");
        const text = document.createElement("td");
        const type = document.createElement("td");
        const badge = document.createElement("span");
        const labels = finding.types.map((item) => RISK_DEFINITIONS[item][lang][0]);

        date.textContent = finding.date || "";
        text.textContent = finding.text || "";
        badge.className = `risk-badge ${getBadgeClass(finding.highestLevel)}`;
        badge.textContent = labels.length > 0 ? labels.join(", ") : copy.noPii;

        type.append(badge);
        row.append(date, text, type);
        body.append(row);
    });
}

function getCounts(findings, totalScanned) {
    const counts = { safe: 0, 1: 0, 2: 0, 3: 0 };

    findings.forEach((finding) => {
        if (finding.highestLevel > 0) {
            counts[finding.highestLevel] += 1;
        } else {
            counts.safe += 1;
        }
    });

    if (Number.isFinite(totalScanned) && totalScanned > findings.length) {
        counts.safe += totalScanned - findings.length;
    }

    return counts;
}

function renderDonut(counts) {
    const donut = document.getElementById("result-chart-donut");
    const segments = [
        { count: counts.safe, color: "var(--green-success-lightmode)" },
        { count: counts[1], color: "var(--yellow-primary-lightmode)" },
        { count: counts[2], color: "var(--red-error-lightmode)" },
        { count: counts[3], color: "var(--red-error-lightmode)" }
    ].filter((segment) => segment.count > 0);

    if (!donut) return;

    const total = segments.reduce((sum, segment) => sum + segment.count, 0);

    if (total === 0) {
        donut.style.background = "conic-gradient(var(--green-success-lightmode) 0deg 360deg)";
        return;
    }

    let cursor = 0;
    const gap = segments.length > 1 ? 6 : 0;
    const gradient = segments.map((segment) => {
        const start = cursor;
        const width = (segment.count / total) * 360;
        const end = Math.max(start, start + width - gap);
        cursor += width;
        return `${segment.color} ${start}deg ${end}deg, transparent ${end}deg ${cursor}deg`;
    });

    donut.style.background = `conic-gradient(${gradient.join(", ")})`;
}

function renderLegend(counts, copy) {
    const legend = document.getElementById("result-chart-legend");

    if (!legend) return;

    legend.textContent = "";

    const title = document.createElement("h2");
    title.className = "result-chart__title";
    title.id = "result-chart-title";
    title.textContent = copy.scoreTitle;
    legend.append(title);

    [
        { key: 3, className: "result-chart__swatch--level-three" },
        { key: 2, className: "result-chart__swatch--level-two" },
        { key: 1, className: "result-chart__swatch--level-one" },
        { key: "safe", className: "result-chart__swatch--safe" }
    ].forEach((item) => {
        const row = document.createElement("span");
        const swatch = document.createElement("span");
        const label = document.createElement("span");
        const tooltip = document.createElement("span");

        row.className = "result-chart__legend-item";
        row.tabIndex = 0;
        swatch.className = `result-chart__swatch ${item.className}`;
        label.textContent = `${copy.labels[item.key]} (${counts[item.key] || 0})`;
        tooltip.className = "result-chart__tooltip";
        tooltip.textContent = copy.legend[item.key];

        row.append(swatch, label, tooltip);
        legend.append(row);
    });
}

function renderResults() {
    const lang = getLanguage();
    const copy = RESULT_COPY[lang];
    const data = getData();
    const findings = enrichFindings(data.findings);
    const combos = detectCombos(findings);
    const riskMap = new Map();

    findings.forEach((finding) => {
        finding.types.forEach((type) => {
            const definition = RISK_DEFINITIONS[type];
            riskMap.set(type, {
                id: type,
                level: definition.level,
                label: definition[lang][0],
                summary: definition[lang][1]
            });
        });
    });

    combos.forEach((combo) => {
        riskMap.set(combo.id, {
            id: combo.id,
            level: combo.level,
            label: combo[lang][0],
            summary: combo[lang][1]
        });
    });

    const risks = [...riskMap.values()].sort((a, b) => b.level - a.level);
    const counts = getCounts(findings, data.totalScanned);

    setSummary(data, risks, lang, copy);
    setRiskList(risks, copy);
    renderTable(findings, lang, copy);
    renderDonut(counts);
    renderLegend(counts, copy);
}

document.addEventListener("DOMContentLoaded", renderResults);
