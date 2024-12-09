var predefinedFieldSets = {
  "CVCanadienPeerReviewedArticles": `fieldNames;starts;startColors;ends;endsColors;
title;label="Articles de revue" recordId="[a-z0-9]*?">\\s*<field id="[a-z0-9]*?" label="Titre de l'article">\\s*<value type="String">;rgb(189, 200, 124);</value>;rgb(244, 246, 233);
journal;label="Revue">\\s*<value type="String">;rgb(63, 245, 171);</value>;rgb(220, 253, 240);
volume;label="Volume">\\s*<value type="String">;rgb(219, 88, 142);</value>;rgb(246, 212, 226);
numero;label="Numéro">\\s*<value type="String">;rgb(98, 134, 214);</value>;rgb(208, 219, 243);
pages;label="Plage de page">\\s*<value type="String">;rgb(84, 237, 242);</value>;rgb(226, 252, 253);
status;label="Statut de publication"/?>\\s*(<lov id="[a-z0-9]*">)?;rgb(145, 212, 234);(</lov>)?</?field;rgb(195, 231, 244);
year;label="Année">\\s*<value format="yyyy" type="Year">;rgb(193, 120, 179);</value>;rgb(234, 209, 229);
publisher;label="Éditeur">\\s*<value type="String">;rgb(82, 188, 247);</value>;rgb(197, 233, 252);
url;label="URL">\\s*<value type="String">;rgb(109, 189, 116);</value>;rgb(224, 241, 225);
author;label="Auteurs">\\s*<value type="String">;rgb(81, 205, 111);</value>;rgb(229, 247, 233);
editor;label="Éditeurs">\\s*<value type="String">;rgb(92, 220, 110);</value>;rgb(211, 246, 216);
doi;label="DOI">\\s*<value type="String">;rgb(91, 112, 227);</value>;rgb(192, 200, 244);`,

  "CVCanadienArticlesDeMagazine": `fieldNames;starts;startColors;ends;endsColors;
title;label="Articles de magazine" recordId="[a-z0-9]*?">\\s*<field id="[a-z0-9]*?" label="Titre de l'article">\\s*<value type="String">;rgb(189, 200, 124);</value>;rgb(244, 246, 233);
magazine;label="Nom du magazine">\\s*<value type="String">;rgb(63, 245, 171);</value>;rgb(220, 253, 240);
volume;label="Volume">\\s*<value type="String">;rgb(219, 88, 142);</value>;rgb(246, 212, 226);
numero;label="Numéro">\\s*<value type="String">;rgb(98, 134, 214);</value>;rgb(208, 219, 243);
pages;label="Plage de page">\\s*<value type="String">;rgb(84, 237, 242);</value>;rgb(226, 252, 253);
status;label="Statut de publication"/?>\\s*(<lov id="[a-z0-9]*">)?;rgb(145, 212, 234);(</lov>)?</?field;rgb(195, 231, 244);
year;label="Année">\\s*<value format="yyyy" type="Year">;rgb(193, 120, 179);</value>;rgb(234, 209, 229);
publisher;label="Éditeur">\\s*<value type="String">;rgb(82, 188, 247);</value>;rgb(197, 233, 252);
url;label="URL">\\s*<value type="String">;rgb(109, 189, 116);</value>;rgb(224, 241, 225);
author;label="Auteurs">\\s*<value type="String">;rgb(81, 205, 111);</value>;rgb(229, 247, 233);
editor;label="Éditeurs">\\s*<value type="String">;rgb(92, 220, 110);</value>;rgb(211, 246, 216);
doi;label="DOI">\\s*<value type="String">;rgb(91, 112, 227);</value>;rgb(192, 200, 244);`,

  "CVCanadienArticlesDeConference": `fieldNames;starts;startColors;ends;endsColors;
type;label="Articles de conférence" recordId="[a-z0-9]*">\\s*<field id="[a-z0-9]*" label="Type de publication de conférence"/?>\\s*(<lov id="[a-z0-9]*">)?;rgb(229, 104, 183);(</lov>)?</?field;rgb(251, 230, 243);
title;label="Titre de l'article">\\s*<value type="String">;rgb(189, 200, 124);</value></field>;rgb(244, 246, 233);
conference_name;label="Nom de la conférence"/?>\\s*<value type="String">;rgb(63, 245, 171);</value></field>;rgb(220, 253, 240);
location;label="Lieu de la conférence"/?>\\s*(<lov id="[a-z0-9]*">)?;rgb(219, 88, 142);(</lov>)?</?field;rgb(246, 212, 226);
town;label="Ville">\\s*<value type="String">;rgb(98, 134, 214);</value></field>;rgb(208, 219, 243);
conference_date;label="Date de la conférence">\\s*<value format="yyyy/MM" type="YearMonth">;rgb(154, 202, 185);</value></field>;rgb(229, 241, 237);
title_proceeding;label="Publié dans">\\s*<value type="String">;rgb(153, 217, 139);</value></field>;rgb(206, 237, 199);
pages;label="Plage de page">\\s*<value type="String">;rgb(84, 237, 242);</value></field>;rgb(226, 252, 253);
status;label="Statut de publication"/?>\\s*(<lov id="[a-z0-9]*">)?;rgb(145, 212, 234);(</lov>)?</?field;rgb(195, 231, 244);
year;label="Année">\\s*<value format="yyyy" type="Year">;rgb(193, 120, 179);</value></field>;rgb(234, 209, 229);
publisher;label="Éditeur">\\s*<value type="String">;rgb(82, 188, 247);</value></field>;rgb(197, 233, 252);
url;label="URL">\\s*<value type="String">;rgb(109, 189, 116);</value></field>;rgb(224, 241, 225);
peer_reviewed;label="Revu par un comité de lecture\\?"/?>(<lov id="[a-z0-9]*">)?;rgb(159, 135, 209);(</lov>)?</?field;rgb(230, 223, 243);
invited_speaker;label="Étiez-vous un conférencier invité\\?"/?>(<lov id="[a-z0-9]*">)?;rgb(226, 133, 243);(</lov>)?</?field;rgb(242, 201, 250);
author;label="Auteurs">\\s*<value type="String">;rgb(81, 205, 111);</value></field>;rgb(229, 247, 233);
editor;label="Éditeurs">\\s*<value type="String">;rgb(92, 220, 110);</value></field>;rgb(211, 246, 216);
doi;label="DOI">\\s*<value type="String">;rgb(91, 112, 227);</value></field>;rgb(192, 200, 244);`,

  "CVCanadienFinancement": `fieldNames;starts;startColors;ends;endsColors;
type_financement;label="Historique du financement de la recherche" recordId="[a-z0-9]*"><field id="[a-z0-9]*" label="Type de financement"><lov id="[a-z0-9]*">;rgb(235, 211, 144);</lov></field>;rgb(244, 230, 192);
debut_annee_mois;label="Début de financement"><value format="yyyy/MM" type="YearMonth">;rgb(209, 156, 181);</value>;rgb(246, 236, 241);
fin_annee_mois;label="Fin de financement"><value format="yyyy/MM" type="YearMonth">;rgb(209, 222, 129);</value>;rgb(240, 244, 213);
titre;label="Titre du financement"><value type="String">;rgb(134, 239, 226);</value>;rgb(212, 249, 245);
type_subvention;label="Type de subvention"/?>(<lov id="[a-z0-9]*">)?;rgb(153, 229, 140);(</lov>)?</?field;rgb(220, 246, 215);
description_fr;<french>;rgb(154, 221, 174);</french>;rgb(204, 238, 214);
description_en;<english>;rgb(135, 138, 210);</english>;rgb(224, 224, 243);
prog_rech_clinique;clinique\\?"/?>(<lov id="[a-z0-9]*">)?;rgb(240, 226, 98);(</lov>)?</?field;rgb(252, 249, 225);
statut_financement;label="Statut du financement"/?>(<lov id="[a-z0-9]*">)?;rgb(246, 194, 88);(</lov>)?</?field;rgb(252, 238, 208);
role;label="Rôle"/?>(<lov id="[a-z0-9]*">)?;rgb(240, 234, 113);(</lov>)?</?field;rgb(249, 247, 201);
application_fr;label="Mise en application de la recherche"><value type="Bilingual"></value><bilingual><french>;rgb(158, 110, 231);</french>;rgb(232, 220, 249);
application_en;<english>;rgb(181, 202, 165);</english>;rgb(216, 227, 208);
organisme;label="Organisme de financement"/?>(<lov id="[a-z0-9]*">)?;rgb(229, 179, 107);(</lov>)?</?field;rgb(245, 226, 199);
autre_organisme;label="Autre organisme de financement"><value type="String">;rgb(129, 195, 205);</value></field>;rgb(214, 235, 239);
programme;label="Nom du programme"><value type="String">;rgb(154, 168, 196);</value></field>;rgb(216, 222, 232);
numero_ref_financement;label="Numéro de référence du financement"><value type="String">;rgb(242, 227, 132);</value></field>;rgb(248, 240, 188);
montant_total;label="Montant total"><value type="Number">;rgb(230, 141, 220);</value></field>;rgb(249, 228, 247);
devise_montant_total;label="Devise du montant total"/?>(<lov id="[a-z0-9]*">)?;rgb(114, 238, 219);(</lov>)?</?field;rgb(212, 250, 244);
montant_recu;label="Portion de financement reçu"><value type="Number">;rgb(128, 172, 237);</value></field>;rgb(204, 222, 248);
devise_montant_recu;label="Devise de la portion de financement reçu"/?>(<lov id="[a-z0-9]*">)?;rgb(250, 119, 247);(</lov>)?</?field;rgb(254, 217, 253);
renouvelable;renouvelable\\?"/?>(<lov id="[a-z0-9]*">)?;rgb(231, 138, 153);(</lov>)?</?field;rgb(245, 206, 212);
competitif;compétitif\\?"/?>(<lov id="[a-z0-9]*">)?;rgb(166, 122, 236);(</lov>)?</?field;rgb(215, 195, 246);
debut_financement_annee_mois;label="Date de début du financement"><value format="yyyy/MM" type="YearMonth">;rgb(121, 247, 193);</value></field>;rgb(222, 253, 240);
fin_financement_annee_mois;label="Date de fin du financement"><value format="yyyy/MM" type="YearMonth">;rgb(220, 186, 140);</value></field>;rgb(242, 230, 214);
autres_chercheurs;label="Nom du chercheur"><value type="String">;rgb(220, 186, 140);</value></field>;rgb(242, 230, 214);`,

  "CVCanadienEncadrementEtudiants": `fieldNames;starts;startColors;ends;endsColors;
role;<section id="[a-z0-9]*" label="Supervision d'étudiants ou de stagiaires postdoctoraux" recordId="[a-z0-9]*"><field id="[a-z0-9]*" label="Rôle"><lov id="[a-z0-9]*">;rgb(137, 218, 187);</lov></field>;rgb(223, 245, 236);
date_debut_encadrement;<field id="[a-z0-9]*" label="Date de début de l'encadrement"><value format="yyyy/MM" type="YearMonth">;rgb(209, 165, 180);</value></field>;rgb(242, 230, 234);
date_fin_encadrement;<field id="[a-z0-9]*" label="Date de fin de l'encadrement"><value format="yyyy/MM" type="YearMonth">;rgb(144, 144, 237);</value></field>;rgb(229, 229, 251);
nom_etudiant;<field id="[a-z0-9]*" label="Etudiant"><value type="String">;rgb(172, 145, 193);</value></field>;rgb(219, 207, 228);
institution_etudiant;<field id="[a-z0-9]*" label="Institution de l'étudiant"><value type="String">;rgb(159, 199, 155);</value></field>;rgb(225, 238, 224);
statut_citoyennete;<field id="[a-z0-9]*" label="Statut de citoyenneté canadienne de l'étudiant"/?>(<lov id="[a-z0-9]*">)?;rgb(227, 154, 168);(</lov>)?</?fi;rgb(243, 211, 216);
type_diplome;eld id="[a-z0-9]*" label="Type de diplôme ou statut postdoctoral"/?>(<lov id="[a-z0-9]*">)?;rgb(135, 160, 209);(</lov>)?</?fi;rgb(207, 217, 237);
avancement;eld id="[a-z0-9]*" label="Statut de l'étudiant"/?>(<lov id="[a-z0-9]*">)?;rgb(191, 184, 148);(</lov>)?</?fi;rgb(239, 238, 229);
date_debut_diplome;eld id="[a-z0-9]*" label="Date de début du diplôme de l'étudiant"><value format="yyyy/MM" type="YearMonth">;rgb(173, 221, 158);</value></field>;rgb(233, 246, 229);
date_diplome;<field id="[a-z0-9]*" label="Date d'obtention du diplôme de l'étudiant"><value format="yyyy/MM" type="YearMonth">;rgb(153, 173, 229);</value></field>;rgb(222, 228, 246);
date_diplome_prevue;<field id="[a-z0-9]*" label="Date prévue pour l'obtention du diplôme de l'étudiant"><value format="yyyy/MM" type="YearMonth">;rgb(175, 222, 142);</value></field>;rgb(233, 246, 224);
titre_projet;<field id="[a-z0-9]*" label="Titre de la thèse ou du projet"><value type="String">;rgb(218, 156, 140);</value></field>;rgb(237, 207, 200);
description_fr;<field id="[a-z0-9]*" label="Description du projet"><value type="Bilingual">(</value><bilingual><french>)?;rgb(232, 186, 115);(</french>)?(</field>)?;rgb(249, 238, 221);
description_en;(<english>)?;rgb(157, 172, 202);(</english>)?(</bilingual></field>)?;rgb(228, 232, 240);
poste_actuel;<field id="[a-z0-9]*" label="Poste actuel"><value type="String">;rgb(220, 231, 112);</value></field>;rgb(244, 247, 209);
nom_diplome;<field id="[a-z0-9]*" label="Nom de diplôme"><value type="Bilingual"></value><bilingual/>;rgb(124, 154, 236);</field>;rgb(194, 208, 246);
specialisation_fr;<field id="[a-z0-9]*" label="Spécialisation"><value type="Bilingual"></value><bilingual/?>(<french>)?;rgb(211, 157, 179);(</french>)?(</field>)?;rgb(240, 221, 229);
specialisation_en;(<english>)?;rgb(144, 233, 231);(</english>)?(</bilingual></field>)?;rgb(219, 248, 247);
organisme;<field id="[a-z0-9]*" label="Organisme actuel"><value type="String">;rgb(215, 236, 134);</value></field>;rgb(243, 249, 219);`,

  "CVCanadienEncadrementEtudiantsNomPrenom": `fieldNames;starts;startColors;ends;endsColors;
role;<section id="[a-z0-9]*" label="Supervision d'étudiants ou de stagiaires postdoctoraux" recordId="[a-z0-9]*"><field id="[a-z0-9]*" label="Rôle"><lov id="[a-z0-9]*">;rgb(137, 218, 187);</lov></field>;rgb(223, 245, 236);
date_debut_encadrement;<field id="[a-z0-9]*" label="Date de début de l'encadrement"><value format="yyyy/MM" type="YearMonth">;rgb(209, 165, 180);</value></field>;rgb(242, 230, 234);
date_fin_encadrement;<field id="[a-z0-9]*" label="Date de fin de l'encadrement"><value format="yyyy/MM" type="YearMonth">;rgb(144, 144, 237);</value></field>;rgb(229, 229, 251);
nom_etudiant;<field id="[a-z0-9]*" label="Etudiant"><value type="String">;rgb(172, 145, 193);(?= );rgb(219, 207, 228);
prenom_etudiant; ;rgb(207, 172, 181);</value></field>;rgb(231, 214, 218);
institution_etudiant;<field id="[a-z0-9]*" label="Institution de l'étudiant"><value type="String">;rgb(159, 199, 155);</value></field>;rgb(225, 238, 224);
statut_citoyennete;<field id="[a-z0-9]*" label="Statut de citoyenneté canadienne de l'étudiant"/?>(<lov id="[a-z0-9]*">)?;rgb(227, 154, 168);(</lov>)?</?fi;rgb(243, 211, 216);
type_diplome;eld id="[a-z0-9]*" label="Type de diplôme ou statut postdoctoral"/?>(<lov id="[a-z0-9]*">)?;rgb(135, 160, 209);(</lov>)?</?fi;rgb(207, 217, 237);
avancement;eld id="[a-z0-9]*" label="Statut de l'étudiant"/?>(<lov id="[a-z0-9]*">)?;rgb(191, 184, 148);(</lov>)?</?fi;rgb(239, 238, 229);
date_debut_diplome;eld id="[a-z0-9]*" label="Date de début du diplôme de l'étudiant"><value format="yyyy/MM" type="YearMonth">;rgb(173, 221, 158);</value></field>;rgb(233, 246, 229);
date_diplome;<field id="[a-z0-9]*" label="Date d'obtention du diplôme de l'étudiant"><value format="yyyy/MM" type="YearMonth">;rgb(153, 173, 229);</value></field>;rgb(222, 228, 246);
date_diplome_prevue;<field id="[a-z0-9]*" label="Date prévue pour l'obtention du diplôme de l'étudiant"><value format="yyyy/MM" type="YearMonth">;rgb(175, 222, 142);</value></field>;rgb(233, 246, 224);
titre_projet;<field id="[a-z0-9]*" label="Titre de la thèse ou du projet"><value type="String">;rgb(218, 156, 140);</value></field>;rgb(237, 207, 200);
description_fr;<field id="[a-z0-9]*" label="Description du projet"><value type="Bilingual">(</value><bilingual><french>)?;rgb(232, 186, 115);(</french>)?(</field>)?;rgb(249, 238, 221);
description_en;(<english>)?;rgb(157, 172, 202);(</english>)?(</bilingual></field>)?;rgb(228, 232, 240);
poste_actuel;<field id="[a-z0-9]*" label="Poste actuel"><value type="String">;rgb(220, 231, 112);</value></field>;rgb(244, 247, 209);
nom_diplome;<field id="[a-z0-9]*" label="Nom de diplôme"><value type="Bilingual"></value><bilingual/>;rgb(124, 154, 236);</field>;rgb(194, 208, 246);
specialisation_fr;<field id="[a-z0-9]*" label="Spécialisation"><value type="Bilingual"></value><bilingual/?>(<french>)?;rgb(211, 157, 179);(</french>)?(</field>)?;rgb(240, 221, 229);
specialisation_en;(<english>)?;rgb(144, 233, 231);(</english>)?(</bilingual></field>)?;rgb(219, 248, 247);
organisme;<field id="[a-z0-9]*" label="Organisme actuel"><value type="String">;rgb(215, 236, 134);</value></field>;rgb(243, 249, 219);`,
 
  "CVCanadienPresentations": `fieldNames;starts;startColors;ends;endsColors;
titre;<section id="[a-z0-9]*" label="Présentations" recordId="[a-z0-9]*"><field id="[a-z0-9]*" label="Titre de la présentation"><value type="String">;rgb(157, 129, 208);</value></field>;rgb(217, 206, 237);
nom_evenement;<field id="[a-z0-9]*" label="Nom de conférence ou d'événement"><value type="String">;rgb(115, 242, 149);</value></field>;rgb(212, 251, 223);
lieu;<field id="[a-z0-9]*" label="Lieu"/?>(<lov id="[a-z0-9]*">)?;rgb(91, 123, 242);(</lov>)?</?fi;rgb(200, 211, 251);
ville;eld id="[a-z0-9]*" label="Ville"><value type="String">;rgb(241, 225, 141);</value></field>;rgb(248, 240, 199);
type_public;<field id="[a-z0-9]*" label="Public principal"/?>(<lov id="[a-z0-9]*">)?;rgb(146, 204, 166);(</lov>)?</?fi;rgb(218, 238, 225);
invite;eld id="[a-z0-9]*" label="Étiez-vous conférencier invité\\?"/?>(<lov id="[a-z0-9]*">)?;rgb(212, 129, 167);(</lov>)?</?fi;rgb(240, 211, 224);
principal;eld id="[a-z0-9]*" label="Étiez-vous conférencier principal\\?"/?>(<lov id="[a-z0-9]*">)?;rgb(164, 218, 114);(</lov>)?</?fi;rgb(224, 242, 207);
concours;eld id="[a-z0-9]*" label="S'agissait-il d'une présentation dans le cadre d'un concours\\?"/?>(<lov id="[a-z0-9]*">)?;rgb(162, 206, 200);(</lov>)?</?field;rgb(221, 237, 235);
annee; id="[a-z0-9]*" label="Année de présentation"><value format="yyyy" type="Year">;rgb(139, 232, 107);</value></field>;rgb(221, 248, 211);
url;<field id="[a-z0-9]*" label="URL"><value type="String">;rgb(198, 205, 169);</value></field>;rgb(231, 234, 219);
copresentateurs;<field id="[a-z0-9]*" label="Co-présentateurs"><value type="String">;rgb(196, 228, 143);</value></field>;rgb(227, 242, 201);`,

  "CVCanadienInterventionsMedias": `fieldNames;starts;startColors;ends;endsColors;
sujet;<section id="[a-z0-9]*" label="Entrevues (diffusées|publiées)" recordId="[a-z0-9]*"><field id="[a-z0-9]*" label="Sujet"><value type="String">;rgb(204, 208, 169);</value></field>;rgb(235, 236, 220);
intervieweur;<field id="[a-z0-9]*" label="Intervieweur"><value type="String">;rgb(204, 189, 166);</value></field>;rgb(234, 229, 219);
emission;<field id="[a-z0-9]*" label="Émission"><value type="String">;rgb(221, 138, 146);</value></field>;rgb(238, 197, 201);
media;<field id="[a-z0-9]*" label="(Chaîne|Tribune)"><value type="String">;rgb(202, 165, 207);</value></field>;rgb(232, 216, 234);
date_premiere_diffusion;<field id="[a-z0-9]*" label="Date de (la première diffusion|publication)"><value format="yyyy-MM-dd" type="Date">;rgb(148, 207, 128);</value></field>;rgb(233, 245, 229);
date_derniere_diffusion;<field id="[a-z0-9]*" label="Date de la dernière diffusion"><value format="yyyy-MM-dd" type="Date">;rgb(139, 167, 235);</value></field>;rgb(219, 228, 249);
url;<field id="[a-z0-9]*" label="URL"><value type="String">;rgb(211, 135, 156);</value></field></section>;rgb(245, 228, 233);`,

  "ArticlesFromGoogleScholar": `fieldNames;starts;startColors;ends;endsColors;
title;<td class="gsc_a_t"><a href=".*?" class="gsc_a_at">;rgb(108, 243, 243);</a>;rgb(231, 253, 253);
authors;<div class="gs_gray">;rgb(227, 230, 153);</div>(<div class="gs_gray">)?;rgb(246, 247, 221);
year;<td class="gsc_a_y"><span class="gsc_a_h gsc_a_hc gs_ibl">;rgb(204, 197, 166);</span>;rgb(237, 234, 223);`,
}


String.prototype.replaceAt = function (str, repl, idx) {
  var firstPart = this.substring(0, idx);
  var remainingPart = this.substring(idx, this.length);
  var pos = remainingPart.indexOf(str);
  var result = firstPart;
  if (pos > -1) {
    result = result + remainingPart.substring(0, pos) + repl;
  }
  result = result + remainingPart.substring(pos + str.length, remainingPart.length);
  return result;
}

getSisterColors = () => {
  var hue = 360 * Math.random();
  var sat = 25 + 70 * Math.random();
  var col1 = "hsl(" + hue + ',' + sat + '%,' +
    (85 + 10 * Math.random()) + '%)';
  var col2 = "hsl(" + hue + ',' + sat + '%,' +
    (65 + 10 * Math.random()) + '%)';
  return [col1, col2]
}

// search for a string in normal or regex mode
// returns an object
//   str: the found string
//   index: the position of the found string
//   lastIndex: the position of the end of the found string
String.prototype.findFirstAt = function (search, pos = 0, regex = false) {
  var result = { str: null, index: 0, lastIndex: 0 };
  pos = (pos < 0 ? 0 : pos);
  if (regex && search && search != '') {
    try {
      const startRE = new RegExp(search, 'g');
      foundArr = startRE.exec(this.substring(pos));
      if (foundArr) {
        result.str = foundArr[0];
        result.index = pos + foundArr.index;
        result.lastIndex = pos + startRE.lastIndex;
      }
    } catch (e) {
    }
  }
  else if (search && search != '') {
    var idx = this.substring(pos).indexOf(search);
    if (idx > -1) {
      result.str = search;
      result.index = pos + idx;
      result.lastIndex = pos + idx + search.length;
    }
  }
  return result;
}

function isEmpty(obj) {
  if (!obj || (obj &&
    Object.keys(obj).length === 0 &&
    Object.getPrototypeOf(obj) === Object.prototype)) {
    return true;
  }
  return false;
}

var extractValues = (source = '', fieldDefsArr = []) => {
  var someThingFound;
  var currentPos = 0;
  var lastRow = {};
  var fieldNames = [];
  var highLightedSource = source ? source.replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('\n', '<br>') : '';
  var selectedPos = 0;
  var lastFieldName = '';
  var firstWasFound = false;

  // create an array of all field names
  fieldDefsArr.forEach((fieldDef) => {
    fieldNames.push(fieldDef.fieldName);
  });

  // begin writing  the CSV file
  var resultCSV = fieldNames.length > 0 ? fieldNames.join(";") + ";\n" : "";

  /* logic goes like this:

   - find the first start delimiter and the corresponding end delimiter
   - set the current position to the end of this find
   - extract the value
   - remember the value till the next find in case it gets repeated
   - if the found field is not the same as the previous one add the last one to the current row
   - if the found fied is a start field, save the last row (and reset the currnt one)
  */

  var fieldIdx = 0;
  do {
    someThingFound = false;
    var bestFoundPos = source ? source.length : 0;
    var currentFind = {};
    var start = {};

    // search for next closest delimiter starting with the lst found field (to allow for repeatitions).
    // stop after finding one.
    // i.e. the follwwing sequence: <1>a</1><2>b</2><2>b</2><3>c</3><2>b</2><1>e</1>
    //      will result in 'f1;f2;f3;\na;b, b;c;\ne;;;\n'
    //      not 'f1;f2;f3;\na;b, b, b;c;\ne;;;\n'
    // have a look at test below for more examples
    if (fieldDefsArr.length > 0) {
      currentFind = {};
      var notLastFound = false;
      var searchIdx = fieldIdx % fieldDefsArr.length;
      do {
        var currentField = fieldDefsArr[searchIdx % fieldDefsArr.length];
        start = source.findFirstAt(currentField.start, currentPos, true);
        if (start.str && start.index < bestFoundPos) {
          someThingFound = true;
          bestFoundPos = start.index;

          currentFind = {};
          currentFind.fieldName = currentField.fieldName;
          currentFind.start = start;
          currentFind.start.col = currentField.startCol;

          var end = source.findFirstAt(currentField.end, start.lastIndex, true);
          if (end.str !== null) {
            currentFind.end = end;
            currentFind.end.col = currentField.endCol;
          }

          if (searchIdx > fieldIdx) {
            notLastFound = true;
          }

          fieldIdx = searchIdx;
        }
        searchIdx++;
      }
      while (searchIdx <= fieldDefsArr.length && !notLastFound)
    }

    if (!isEmpty(currentFind)) {
      // highlight the find
      if (!isEmpty(currentFind.start) && currentFind.start.str) {
        // HTML encode the start string 
        var searchStr = currentFind.start.str.replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('\n', '<br>');
        var repl = '<span style="background-color: ' + currentFind.start.col + '">' + searchStr + '</span>';
        highLightedSource = highLightedSource.replaceAt(searchStr, repl, selectedPos);
        selectedPos = highLightedSource.indexOf(repl, selectedPos) + repl.length;

        currentPos = currentFind.start.lastIndex;

        if (!isEmpty(currentFind.end)) {
          // HTML encode the end string 
          searchStr = currentFind.end.str.replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('\n', '<br>');
          var repl = '<span style="background-color: ' + currentFind.end.col + '">' + searchStr + '</span>';
          highLightedSource = highLightedSource.replaceAt(searchStr, repl, selectedPos);
          selectedPos = highLightedSource.indexOf(repl, selectedPos) + repl.length;

          // if the current field is a starter field, push the last row in the data array
          // and reset the lastRow object
          if (currentFind.fieldName == fieldDefsArr[0].fieldName) {
            firstWasFound = true;
            if (!isEmpty(lastRow)) {
              fieldNames.forEach((name) => {
                resultCSV += lastRow[name] ? lastRow[name] + ';' : ";"
              });
              resultCSV += '\n';
              lastRow = {};
            }
          }

          // if the field does not repeat itself just add it
          if (firstWasFound) {
            // extract the value
            var value = source.substring(currentFind.start.lastIndex, currentFind.end.index);
            // if a separator or a newline char is found in the value, escape double quotes and double quote the value
            if (value.indexOf(';') > -1 || value.indexOf('\n') > -1) {
              value = '"' + value.replaceAll('"', '""') + '"';
            }
            
            // add it as a repeated value or not
            if (lastRow[currentFind.fieldName] == undefined) {
              lastRow[currentFind.fieldName] = value;
              lastFieldName = currentFind.fieldName;
            }
            else if (currentFind.fieldName == lastFieldName) {
              if (lastRow[currentFind.fieldName] == '') {
                lastRow[currentFind.fieldName] = value;
              }
              else if (value != '') {
                lastRow[currentFind.fieldName] = lastRow[currentFind.fieldName] + ', ' + value;
              }
            }
            else {
              firstWasFound = false;
            }
            // update the pointer
            currentPos = currentFind.end.lastIndex;
          }
          else {
            fieldIdx = 0;
          }

        }
      }
    }
  } while (someThingFound);

  // add the remaining values
  if (!isEmpty(lastRow)) {
    fieldNames.forEach((name) => {
      resultCSV += lastRow[name] ? lastRow[name] + ';' : ";"
    });
    resultCSV += '\n';
  }

  return {
    resultCSV: resultCSV,
    highLightedSource: highLightedSource
  };
}

/*
// tests
var source = '<f1>a</f1>';

// 1) test empty
var vals3 = extractValues();
if (vals3.resultCSV != '') {
  throw new Error("Test 1 failed!");
}

// 2) test with source only
vals3 = extractValues('aa');
if (vals3.resultCSV != '') {
  throw new Error("Test 2 failed!");
}
// 3) test with source and field names only
source = 'aa';
var f1 = ['f1'];
var newf1 = [{ fieldName: f1 }];
vals3 = extractValues(source, newf1);
if (vals3.resultCSV != 'f1;\n') {
  throw new Error("Test 3 failed!");
}

// 4) simple example
source = '<1>a</1><1>b</1>';
var f1Start = ['<1>'];
var f1StartCol = ['rgb(255, 0, 0)'];
var f1End = ['</1>'];
var f1EndCol = ['rgb(0, 0, 255)'];
newf1 = [{
  fieldName: f1[0], start: f1Start[0], startCol: f1StartCol[0], end: f1End[0], endCol: f1EndCol[0]
}];
vals3 = extractValues(source, newf1);
if (vals3.resultCSV != 'f1;\na;\nb;\n') {
  throw new Error("Test 4 failed!");
}

// 5) with 2 columns
source = '<1>a</1><2>b</2><1>c</1>';
var f2 = ['f2'];
var f2Start = ['<2>'];
var f2StartCol = ['rgb(255, 0, 0)'];
var f2End = ['</2>'];
var f2EndCol = ['rgb(0, 0, 255)'];
newf1 = [
  ...newf1,
  { fieldName: f2[0], start: f2Start[0], startCol: f2StartCol[0], end: f2End[0], endCol: f2EndCol[0] }
];
vals3 = extractValues(source, newf1);
if (vals3.resultCSV != 'f1;f2;\na;b;\nc;;\n') {
  throw new Error("Test 5 failed!");
}

// 6) with 2 columns but starting with a non starting field
source = '<2>x</2><1>a</1><2>b</2><1>c</1>';
vals3 = extractValues(source, newf1);
if (vals3.resultCSV != 'f1;f2;\na;b;\nc;;\n') {
  throw new Error("Test 6 failed!");
}

// 7) with 2 columns, one repeating 2 times
source = '<1>a</1><2>b</2><2>b</2><1>c</1>';
vals3 = extractValues(source, newf1);
if (vals3.resultCSV != 'f1;f2;\na;b, b;\nc;;\n') {
  throw new Error("Test 7 failed!");
}

// 8) with 2 columns, one repeating 4 times
source = '<1>a</1><2>b</2><2>b</2><2>b</2><2>b</2><1>c</1>';
vals3 = extractValues(source, newf1);
if (vals3.resultCSV != 'f1;f2;\na;b, b, b, b;\nc;;\n') {
  throw new Error("Test 8 failed!");
}

// 9) with 3 columns, one repeating 2 times only even if it repeats after the third field
source = '<1>a</1><2>b</2><2>b</2><3>c</3><2>d</2><1>e</1>';
newf1 = [
  ...newf1,
  {
    fieldName: 'f3',
    start: '<3>',
    startCol: f2StartCol[0],
    end: '</3>',
    endCol: f2EndCol[0]
  }
];
vals3 = extractValues(source, newf1);
if (vals3.resultCSV != 'f1;f2;f3;\na;b, b;c;\ne;;;\n') {
  throw new Error("Test 9 failed!");
}

// 10) with 3 columns, one repeating 2 times only even if it repeats 2 times after the third field
source = '<1>a</1><2>b</2><2>b</2><3>c</3><2>d</2><2>d</2><1>e</1>';
vals3 = extractValues(source, newf1);
if (vals3.resultCSV != 'f1;f2;f3;\na;b, b;c;\ne;;;\n') {
  throw new Error("Test 10 failed!");
}

// 11) with 3 columns, the 2nd one repeating 2 times and preceded by the 3th column
source = '<1>a</1><3>c</3><2>b</2><2>b</2><3>c</3><1>e</1>';
vals3 = extractValues(source, newf1);
if (vals3.resultCSV != 'f1;f2;f3;\na;b, b;c;\ne;;;\n') {
  throw new Error("Test 11 failed!");
}

// 12) with 3 columns, the 3rd one repeating 2 times but preceded by the 2th column
source = '<1>a</1><2>b</2><3>c</3><2>x</2><3>c</3><1>e</1>';
vals3 = extractValues(source, newf1);
// could also or should be 'f1;f2;f3;\na;b;c;\ne;;;\n'
if (vals3.resultCSV != 'f1;f2;f3;\na;b;c, c;\ne;;;\n') {
  throw new Error("Test 12 failed!");
}

// 13) with 3 columns, the 3rd one repeating 2 times and disrupted by the 2th column
source = '<1>a</1><3>c</3><1>e</1><2>b</2>';
vals3 = extractValues(source, newf1);
if (vals3.resultCSV != 'f1;f2;f3;\na;;c;\ne;b;;\n') {
  throw new Error("Test 13 failed!");
}

// 14) with 3 columns, the 3rd one repeating 2 times and disrupted by the 2th column
source = '<2>b</2><1>a</1><3>c</3><1>e</1>';
vals3 = extractValues(source, newf1); // 'f1;f2;f3;\na;b;c;\ne;;;\n'
if (vals3.resultCSV != 'f1;f2;f3;\na;;c;\ne;;;\n') {
  throw new Error("Test 14 failed!");
}

// 15) try a single char as delimiter
source = '$$AA#BB$$';
var fieldSet = [
  {
    fieldName: 'f1',
    start: '\\$\\$',
    startCol: ['rgb(255, 0, 0)'],
    end: '(?=#)',
    endCol: ['rgb(0, 0, 255)']
  },
  {
    fieldName: 'f2',
    start: '#',
    startCol: ['rgb(255, 0, 0)'],
    end: '\\$\\$',
    endCol: ['rgb(0, 0, 255)']
  }
];
vals3 = extractValues(source, fieldSet); // 'f1;f2;\na;b;\n'
if (vals3.resultCSV != 'f1;f2;\nAA;BB;\n') {
  throw new Error("Test 15 failed!");
}

// 16) try a single char as delimiter
source = 'xaaaaabyc';
var fieldSet = [
  {
    fieldName: 'f1',
    start: 'b',
    startCol: ['rgb(255, 0, 0)'],
    end: 'c',
    endCol: ['rgb(0, 0, 255)']
  },
  {
    fieldName: 'f2',
    start: 'x',
    startCol: ['rgb(255, 0, 0)'],
    end: 'y',
    endCol: ['rgb(0, 0, 255)']
  }
];
vals3 = extractValues(source, fieldSet);
if (vals3.resultCSV != 'f1;f2;\ny;;\n') {
  throw new Error("Test 16 failed!");
}
*/
var getFieldDef = () => {
  var fieldDefsArr = [];
  var allRows = document.getElementsByClassName('fieldDefsRow');
  Array.from(allRows).forEach(function (row) {
    var newFieldDefObj = {};

    newFieldDefObj['fieldName'] = row.querySelector("input[name='fieldname']").value;

    var input = row.querySelector("input[name='start']");
    newFieldDefObj['start'] = input.value;
    newFieldDefObj['startCol'] = window.getComputedStyle(input).getPropertyValue('background-color');

    var input = row.querySelector("input[name='end']");

    newFieldDefObj['end'] = input.value;
    newFieldDefObj['endCol'] = window.getComputedStyle(input).getPropertyValue('background-color');

    fieldDefsArr.push(newFieldDefObj);
  });

  return fieldDefsArr;
}

var addColors = (source, fieldDefs) => {
  fieldDefs.forEach((field) => {
    if (field.start != '') {
      const matches = source.matchAll(field.start);
      var pos = 0;
      for (const match of matches) {
        const repl = '<span style="background-color: ' + field.startCol + '">' + match[0] + '</span>';
        pos = pos + match.index;
        source = source.replaceAt(match[0], repl, pos);
        pos = pos + repl.length - match[0].length;
      }
    }
  });
  return source;
}

var extractThread;
var prepareExtract = () => {
  var source = document.getElementById("sourceinput").value;
  var fieldDef = getFieldDef();

  document.getElementById("resultingCSVinput").value = '';
  document.getElementById("resultingCSVWait").style.display = 'flex';
  document.getElementById("sourceHighlightedWait").style.display = 'flex';
  
  // extract asynchronously to  allow interface changes to happen
  clearTimeout(extractThread);
  extractThread = setTimeout(() => {
    var result = extractValues(source, fieldDef);

    document.getElementById("resultingCSVWait").style.display = 'none';
    document.getElementById("sourceHighlightedWait").style.display = 'none';

    document.getElementById("resultingCSVinput").value = result.resultCSV;
    document.getElementById("sourceHighlighted").innerHTML = result.highLightedSource;

  }, 50);
};

// borrowed from https://stackoverflow.com/questions/1293147/how-to-parse-csv-data
function parseCSV(str, sep = ',') {
  var arr = [];
  var quote = false;  // 'true' means we're inside a quoted field
  var newcol = false;
  // Iterate over each character, keep track of current row and column (of the returned array)
  for (var row = 0, col = 0, c = 0; str && c < str.length; c++) {
    var cc = str[c], nc = str[c + 1];        // Current character, next character

    // If the current character is a not a newline (LF or CR) and we are not in a quoted field create a new column
    if ((cc != '\r' && cc != '\n' && !quote) || newcol) {
      arr[row] = arr[row] || [];             // Create a new row if necessary
      arr[row][col] = arr[row][col] || '';   // Create a new column (start with empty string) if necessary
      newcol = false;
    }

    // If the current character is a quotation mark, and we're inside a
    // quoted field, and the next character is also a quotation mark,
    // add a quotation mark to the current column and skip the next character
    if (cc == '"' && quote && nc == '"') { arr[row][col] += cc; ++c; continue; }

    // If it's just one quotation mark, begin/end quoted field
    //if (cc == '"') { quote = !quote; continue; }
    //if (cc == sep && nc == '"' && !quote) { quote = true; ++col; ++c; continue; }
    if (cc == sep && nc == '"' && !quote) { quote = true; newcol = true; ++col; ++c; continue; }

    if (cc == '"' && (nc == '\n' || nc == '\r') && quote) { quote = false; ++row; col = 0; ++c; continue; }
    if (cc == '"' && (nc == sep || nc == undefined) && quote) { quote = false; ++col; ++c; continue; }

    // If it's a comma and we're not in a quoted field, move on to the next column
    if (cc == sep && !quote) { ++col; continue; }

    // If it's a newline (CRLF) and we're not in a quoted field, skip the next character
    // and move on to the next row and move to column 0 of that new row
    if (cc == '\r' && nc == '\n' && !quote) { ++row; col = 0; ++c; continue; }

    // If it's a newline (LF or CR) and we're not in a quoted field,
    // move on to the next row and move to column 0 of that new row
    if (cc == '\n' && !quote) { ++row; col = 0; continue; }
    if (cc == '\r' && !quote) { ++row; col = 0; continue; }

    // Otherwise, append the current character to the current column
    arr[row][col] += cc;
  }
  return arr;
}

/*
// basic tests
var x = parseCSV('a,bx,c,\n1,2,3,');
x = parseCSV('a\nb');
x = parseCSV('a\nb\n');
x = parseCSV('a\nb\n\n');

x = parseCSV('a,\nb,');
x = parseCSV('a,\nb,\n');
x = parseCSV('a,\nb,\n\n');

// nothing
x = parseCSV();
x = parseCSV('');

// quote alone (no quoted string)
x = parseCSV('a,b,c,\n1,2",3,');

// quote alone before end of line (\n)
x = parseCSV('a,b"\n1,2');

// quote alone before end of line (\r)
x = parseCSV('a,b"\r1,2');

// quote alone at the end of file
x = parseCSV('a,b,\n1,2"');

// quoted string
x = parseCSV('a,b,c,\n1,"2",3,');

// true quoted string before \n
x = parseCSV('a,"b"\n1,2');

// true quoted string having a \n
x = parseCSV('a,"b\nc"\n1,2');

// true quoted string having an escaped quote
x = parseCSV('a,"b""c"\n1,2');

// true quoted string having an escaped quote
x = parseCSV('a,"b"""\n1,2');

// true quoted string having an escaped quote
x = parseCSV('a,"b"","\n1,2');

// true quoted string having an escaped quote
x = parseCSV('a,"b"","\n1,2');

// true quoted string having an escaped quote
x = parseCSV('a,""","\n1,2');
*/

var setFieldsFromCSV = (csv) => {
  // validate the csv
  //if (csv[0] != ["fieldNames", "starts", "startColors", "ends", "endsColors"]) {
  if (JSON.stringify(csv[0]) !== JSON.stringify(["fieldNames", "starts", "startColors", "ends", "endsColors"])) {
    alert('Invalid CSV');
    return;
  }
  // make sure the number of field in the interface is ok
  var allDeleteButtons = document.getElementsByClassName('deleteRowButton');
  while (allDeleteButtons.length != csv.length - 1) {
    if (allDeleteButtons.length < csv.length - 1) {
      // add a row
      addRow(null, false);
    }
    else {
      // delete a row
      //allDeleteButtons[0].click();
      deleteRow(allDeleteButtons[0], false);
    }
    allDeleteButtons = document.getElementsByClassName('deleteRowButton');
  }

  // distribute the values
  var arrIdx = 1;
  Array.from(allDeleteButtons).forEach(function (deleteButton) {
    var parent = deleteButton.closest(".fieldDefsRow");
    parent.querySelector("input[name='fieldname']").value = csv[arrIdx][0];
    parent.querySelector("input[name='start']").value = csv[arrIdx][1];
    parent.querySelector("input[name='start']").style.cssText = 'background-color:' + csv[arrIdx][2];
    parent.querySelector("input[name='end']").value = csv[arrIdx][3];
    parent.querySelector("input[name='end']").style.cssText = 'background-color:' + csv[arrIdx][4];
    arrIdx++;
  });

  prepareExtract();
}

//////////////////////////////////////////////////////////////////////////////
// Add Row
// add a field row before the target if it is provided, otherwise at the end
var addRow = (target, extract = true) => {
  var newRow = document.getElementsByClassName("fieldDefsRow")[0].cloneNode(true);

  // reset the inputs and add the onchange event listener
  var changingfields = newRow.getElementsByClassName("changingfield");
  Array.from(changingfields).forEach(function (element) {
    element.addEventListener('input', prepareExtract);
    element.value = '';
  });

  // add the addRow event listener
  var addRowBeforeButtons = newRow.getElementsByClassName('addBeforeRowButton');
  Array.from(addRowBeforeButtons).forEach(function (element) {
    element.addEventListener('click', addRow);
  });

  // add the delete event listener
  var deleteButtons = newRow.getElementsByClassName('deleteRowButton');
  Array.from(deleteButtons).forEach(function (element) {
    element.addEventListener('click', deleteRow);
  });

  // assign background color to expression inputs
  var cols = getSisterColors();
  newRow.querySelector("input[name='start']").style.cssText = 'background-color:' + cols[1];
  newRow.querySelector("input[name='end']").style.cssText = 'background-color:' + cols[0];

  var parent = document.getElementById('fieldDefsRows');
  if (!target || target.currentTarget.id == "addRowButton") {
    parent.appendChild(newRow);
  } else {
    parent.insertBefore(newRow, target.currentTarget.closest(".fieldDefsRow"));
  }

  // increment the field name
  newRow.querySelector("input[name='fieldname']").value = "field" + parent.childElementCount;

  // enable the delete row button
  var allDeletebuttons = document.getElementsByClassName('deleteRowButton');
  Array.from(allDeletebuttons).forEach(function (element) {
    element.removeAttribute('disabled');
  });

  if (extract) {
    console.log('addRow');
    prepareExtract();
  }
}

//////////////////////////////////////////////////////////////////////////////
// Delete Row
var deleteRow = (el, extract = true) => {
  var allDeletebuttons = document.getElementsByClassName('deleteRowButton');
  if (allDeletebuttons.length == 2) {
    Array.from(allDeletebuttons).forEach(function (element) {
      element.setAttribute('disabled', true);
    });
  }
  if (allDeletebuttons.length > 1) {
    if (el.currentTarget) {
      el = el.currentTarget;
    }
    el.closest(".fieldDefsRow").remove();
  }
  if (extract) {
    console.log('deleteRow');
    prepareExtract();
  }
}

//////////////////////////////////////////////////////////////////////////////
// Save Field Def
var saveFieldDef = () => {
  var fieldDefArr = getFieldDef();
  var fieldDefCSV = 'fieldNames;starts;startColors;ends;endsColors;\n';


  fieldDefArr.forEach(fieldDef => {
    for (const prop in fieldDef) {
      if (Object.hasOwn(fieldDef, prop)) {
        fieldDefCSV += fieldDef[prop] + ";"
      }
    }
    fieldDefCSV += "\n";
  });

  var csvblob = new Blob([fieldDefCSV], { type: 'text/csv' });
  var a = document.createElement('a');
  a.download = 'fieldDefinition.csv';
  a.href = window.URL.createObjectURL(csvblob);
  a.click();
}

//////////////////////////////////////////////////////////////////////////////
// Load Field Def
var loadFieldDef = (e) => {
  var file = e.target.files[0];
  if (!file) {
    return;
  }
  var reader = new FileReader();
  reader.onload = function (input) {
    setFieldsFromCSV(parseCSV(input.target.result, ';'));
    e.target.value = '';
  };
  reader.readAsText(file);
}

//////////////////////////////////////////////////////////////////////////////
// Load Predefned Set
var loadPredefinedFieldSet = () => {
  var selected = document.getElementById('predefinedFieldSetsSelect').value;
  if (predefinedFieldSets[selected]) {
    setFieldsFromCSV(parseCSV(predefinedFieldSets[selected], ';'));
  }
}

//////////////////////////////////////////////////////////////////////////////
// Load Source File
var loadSourceInput = (e) => {
  var file = e.target.files[0];
  if (!file) {
    return;
  }
  var reader = new FileReader();
  reader.onload = function (input) {
    document.getElementById('sourceinput').value = input.target.result;
    e.target.value = '';
    prepareExtract();
  };
  reader.readAsText(file);
}

//////////////////////////////////////////////////////////////////////////////
// Save CSV Result
var saveCSVResults = () => {
  var source = document.getElementById("resultingCSVinput").value;

  var csvblob = new Blob([source], { type: 'text/csv' });
  var a = document.createElement('a');
  a.download = 'resultingCSV.csv';
  a.href = window.URL.createObjectURL(csvblob);
  a.click();
}

//////////////////////////////////////////////////////////////////////////////
// Add listeners
//////////////////////////////////////////////////////////////////////////////

// add onchange listener to all fields
var changingfields = document.getElementsByClassName("changingfield");
Array.from(changingfields).forEach(function (element) {
  element.addEventListener('input', prepareExtract);
});

// add click listener to add before buttons
var addBeforeButtons = document.getElementsByClassName('addBeforeRowButton');
Array.from(addBeforeButtons).forEach(function (element) {
  element.addEventListener('click', addRow);
});

// add click listener to delete buttons
var deleteButtons = document.getElementsByClassName('deleteRowButton');
Array.from(deleteButtons).forEach(function (element) {
  element.addEventListener('click', deleteRow);
});

// add click listener to the last add button
document.getElementById('addRowButton').addEventListener("click", addRow);

// save and load field set
document.getElementById('saveFieldDefButton').addEventListener("click", saveFieldDef);
document.getElementById('loadFieldDefInput').addEventListener("change", loadFieldDef);

// load predefined fields set button
document.getElementById('loadPredefFieldDefButton').addEventListener("click", loadPredefinedFieldSet);

// load source button
document.getElementById('loadSourceInput').addEventListener("change", loadSourceInput);

// save results button
document.getElementById('saveResCSVButton').addEventListener("click", saveCSVResults);

// assign background color to expression inputs
var cols = getSisterColors();
document.getElementsByName("start")[0].style.cssText = 'background-color:' + cols[1];
document.getElementsByName("end")[0].style.cssText = 'background-color:' + cols[0];

// assign default test value to source
/*document.getElementById("sourceinput").value = "<row>\n\
 <f1>data1</f1>\n\
 <f2>data2</f2>\n\
 <f2>data2</f2>\n\
</row>\n\
<row>\n\
 <f1>data3</f1>\n\
 <f2>data4</f2>\n\
</row>\n\
";

document.getElementById("sourceinput").value = "xaywbzxcy";*/
//console.log('main');

prepareExtract();
