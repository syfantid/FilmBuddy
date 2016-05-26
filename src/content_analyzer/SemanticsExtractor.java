package content_analyzer;

import datamuse.DatamuseQuery;
import datamuse.JSONParse;
import java.util.HashMap;

/**
 * Class to extract the semantic related words
 * Created by Sofia on 5/25/2016.
 */
public class SemanticsExtractor {

    private static DatamuseQuery datamuse;
    private static JSONParse parser;
    private static HashMap<String,String[]> semanticWordsDatamuse;
    private static HashMap<String,String[]> semanticWordsSemanticLink;

    static {
        datamuse = new DatamuseQuery();
        parser = new JSONParse();
        semanticWordsDatamuse = new HashMap<>();
        semanticWordsSemanticLink = new HashMap<>();
    }

    /**
     * Finds all the semantically related words
     * @param extendedPlot The extended plot of the film
     * @return The semantics plot of the film
     */
    public static String findSemantics(String extendedPlot) {

        String semanticWords;
        // Find semantically related words based on Datamuse API
        String semanticWordsDatamuse = findDataMuseSemanticsPlot(extendedPlot.split(" "));
        String semanticWordsSemanticLink = findSemanticLinkSemanticsPlot(extendedPlot.split(" "));
        semanticWords = semanticWordsDatamuse + semanticWordsSemanticLink;

        return semanticWords;
    }

    /**
     * Finds all the related words of the extended plot using Datamuse API
     * @param extendedPlot The plot to be expanded with semantic related words
     * @return The semantics plot resulting from Datamuse
     */
    private static String findDataMuseSemanticsPlot(String[] extendedPlot) {
        StringBuilder semanticPlotDataMuse = new StringBuilder();
        String[] relatedWords;
        for(String word : extendedPlot) { // For each word in the plot
            //Check if word has already been queried (saves superfluous server requests by caching)
            if(semanticWordsDatamuse.containsKey(word)) {
                relatedWords = semanticWordsDatamuse.get(word); // Fetch the related words
            } else { // If this is the first instance of this word
                String json = datamuse.findSimilar(word); // Find all related words from Datamuse
                if(!json.equals("[]")) { // If the results are not empty
                    relatedWords = parser.parseWords(json);
                } else { // Result set is empty
                    relatedWords = new String[0];
                }
                semanticWordsDatamuse.put(word, relatedWords);

            }
            if(relatedWords.length != 0) {
                semanticPlotDataMuse.append(relatedWordsToString(relatedWords));
            }
        }
        return  semanticPlotDataMuse.toString();
    }

    /**
     * Finds all the related words of the extended plot using SemanticLink API
     * @param extendedPlot The plot to be expanded with semantic related words
     * @return The semantics plot resulting from SemanticLink
     */
    private static String findSemanticLinkSemanticsPlot(String[] extendedPlot) {
        StringBuilder semanticPlotSemanticLink = new StringBuilder();
        String[] relatedWords;
        for(String word : extendedPlot) { // For each word in the plot
            //Check if word has already been queried (saves superfluous server requests by caching)
            if(semanticWordsSemanticLink.containsKey(word)) {
                relatedWords = semanticWordsSemanticLink.get(word); // Fetch the related words
            } else { // If this is the first instance of this word
                String json = datamuse.findSimilarSemanticLink(word); // Find all related words from Semantic-Link
                if(!json.equals("[]")) {
                    relatedWords = parser.parseWords(json);
                } else {
                    relatedWords = new String[0];
                }
                semanticWordsSemanticLink.put(word, relatedWords);
            }
            if(relatedWords.length != 0) {
                semanticPlotSemanticLink.append(relatedWordsToString(relatedWords));
            }
        }
        return  semanticPlotSemanticLink.toString();
    }

    /**
     * Turns an array of related words into a string separated with spaces
     * @param relatedWords The array of related words
     * @return The string of related words
     */
    private static String relatedWordsToString(String[] relatedWords) {
        StringBuilder semanticPlot = new StringBuilder();
        for (String relatedWord : relatedWords) {
            if (relatedWord != null && !Processor.isStopWord(relatedWord)) {
                semanticPlot.append(relatedWord);
                semanticPlot.append(" ");
            }
        }
        return semanticPlot.toString();
    }
}
